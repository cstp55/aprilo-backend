<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessKnowledgeSource;
use App\Models\KnowledgeSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class KnowledgeSourceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $sources = KnowledgeSource::query()
            ->withCount('chunks')
            ->where('organization_id', $request->user()->organization_id)
            ->latest()
            ->get();

        return response()->json(['sources' => $sources]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'access_scope' => ['required', Rule::in(['all_employees', 'hr_only'])],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $sourceType = $extension === 'md' ? 'markdown' : $extension;

        if (! in_array($sourceType, ['pdf', 'docx', 'txt', 'markdown'], true)) {
            throw ValidationException::withMessages([
                'file' => ['Only PDF, DOCX, TXT, and Markdown files are supported.'],
            ]);
        }

        $path = $file->store('knowledge-sources');

        // Version control duplicate handling
        $originalName = $file->getClientOriginalName();
        $title = $validated['title'];
        $existing = KnowledgeSource::where('organization_id', $request->user()->organization_id)
            ->where(function ($query) use ($title, $originalName) {
                $query->where('title', $title)
                    ->orWhere('metadata->original_name', $originalName);
            })
            ->where('status', '!=', 'inactive')
            ->get();

        $maxVersion = 1;
        foreach ($existing as $oldSource) {
            $oldMeta = $oldSource->metadata ?? [];
            $version = $oldMeta['version'] ?? 1;
            if ($version >= $maxVersion) {
                $maxVersion = $version + 1;
            }

            $oldSource->update([
                'status' => 'inactive',
                'title' => $oldSource->title . " [Archived V{$version}]",
                'metadata' => array_merge($oldMeta, [
                    'replaced_by_version' => $maxVersion,
                    'replaced_at' => now()->toIso8601String(),
                ])
            ]);
        }

        $source = KnowledgeSource::create([
            'organization_id' => $request->user()->organization_id,
            'title' => $title,
            'source_type' => $sourceType,
            'file_path' => $path,
            'status' => 'uploaded',
            'access_scope' => $validated['access_scope'],
            'uploaded_by' => $request->user()->id,
            'metadata' => [
                'original_name' => $originalName,
                'size' => $file->getSize(),
                'version' => $maxVersion,
            ],
        ]);

        if (App::environment(['local', 'testing'])) {
            ProcessKnowledgeSource::dispatchSync($source->id);
        } else {
            ProcessKnowledgeSource::dispatch($source->id);
        }

        return response()->json(['source' => $source->fresh()->loadCount('chunks')], 201);
    }

    public function update(Request $request, KnowledgeSource $source): JsonResponse
    {
        $this->authorizeAdmin($request);
        $this->authorizeOrganization($request, $source);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'access_scope' => ['sometimes', Rule::in(['all_employees', 'hr_only'])],
            'status' => ['sometimes', Rule::in(['uploaded', 'indexing', 'indexed', 'failed', 'inactive'])],
        ]);

        $source->update($validated);

        return response()->json(['source' => $source->fresh()]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(
            in_array($request->user()->role, [UserRole::Owner->value, UserRole::HrAdmin->value], true),
            403,
            'Admin access is required.'
        );
    }

    private function authorizeOrganization(Request $request, KnowledgeSource $source): void
    {
        abort_unless($source->organization_id === $request->user()->organization_id, 404);
    }
}
