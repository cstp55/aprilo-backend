<?php

namespace App\Services\Knowledge;

use App\Models\KnowledgeChunk;
use App\Models\KnowledgeSource;
use App\Models\Organization;
use App\Models\OrganizationSetting;
use App\Services\AI\EmbeddingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class KnowledgeSyncService
{
    public function __construct(
        private readonly EmbeddingService $embeddings,
        private readonly ChunkingService $chunking
    ) {
    }

    /**
     * Synchronize and index an organization's settings fields into the Knowledge Base / Vector DB.
     */
    public function syncSettingsToKnowledgeBase(OrganizationSetting $settings): int
    {
        $org = $settings->organization;
        if (!$org) {
            return 0;
        }

        $orgName = $org->name;
        $orgDetails = trim($settings->organization_details ?? '');
        $systemPrompt = trim($settings->custom_system_prompt ?? '');
        $guardrails = trim($settings->eligibility_criteria ?? '');
        $whatsappNumber = trim($settings->whatsapp_number ?: ($settings->whatsapp_sender_phone ?? ''));
        $whatsappUrl = $settings->getWhatsAppDirectUrl();

        // If all fields are empty, nothing to index
        if (empty($orgDetails) && empty($systemPrompt) && empty($guardrails)) {
            return 0;
        }

        try {
            // Find or create the primary settings knowledge source
            $uploaderId = $org->users()->first()?->id ?? \App\Models\User::first()?->id;

            $source = KnowledgeSource::updateOrCreate(
                [
                    'organization_id' => $org->id,
                    'source_type' => 'settings_sync',
                ],
                [
                    'title' => "{$orgName} - Profile & Services Knowledge",
                    'file_path' => null,
                    'status' => 'indexed',
                    'access_scope' => 'all',
                    'uploaded_by' => $uploaderId,
                    'metadata' => [
                        'synced_at' => now()->toIso8601String(),
                        'generator' => 'KnowledgeSyncService',
                    ],
                ]
            );

            // Clean existing chunks for this settings source
            $source->chunks()->delete();

            $chunkItems = [];

            // 1. Parse and extract clean sections from Organization Details
            if (!empty($orgDetails)) {
                $cleanedDetails = $this->cleanRawMetadataHeaders($orgDetails, $orgName);
                if (!empty($cleanedDetails)) {
                    $detailChunks = $this->chunking->chunk($cleanedDetails, 600, 100);
                    foreach ($detailChunks as $dc) {
                        $chunkItems[] = [
                            'section_title' => "{$orgName} Overview & Services",
                            'text' => $dc,
                        ];
                    }
                }
            }

            // 2. Add domain-specific structured knowledge if medical tourism / patient coordination
            $isHealthcare = str_contains(strtolower($orgName), 'health') || 
                            str_contains(strtolower($orgDetails), 'patient') || 
                            str_contains(strtolower($orgDetails), 'medical') ||
                            str_contains(strtolower($orgDetails), 'hospital');

            if ($isHealthcare) {
                // Medical Visa Assistance Knowledge
                $chunkItems[] = [
                    'section_title' => "{$orgName} Medical Visa Assistance & Travel Planning",
                    'text' => "For international patients seeking medical treatment in India, {$orgName} coordinates official Medical Visa Invitation Letters from accredited partner hospitals. We guide patients and accompanying attendants through medical documentation, invitation letter issuance, travel planning, complimentary airport transfers, and local accommodation arrangements.",
                ];

                // Hospital & Doctor Coordination Knowledge
                $chunkItems[] = [
                    'section_title' => "{$orgName} Hospital & Doctor Coordination",
                    'text' => "{$orgName} facilitates medical consultations and appointments with premier accredited multispecialty hospitals and experienced specialist doctors across India. Patients can submit their medical reports to receive doctor opinions and transparent treatment cost estimates prior to traveling.",
                ];
            }

            // 3. Contact & WhatsApp coordination chunk
            if (!empty($whatsappUrl) || !empty($whatsappNumber)) {
                $contactText = "For direct assistance and consultation with {$orgName}, visitors can connect with the coordination team via official WhatsApp";
                if (!empty($whatsappNumber)) {
                    $contactText .= " at {$whatsappNumber}";
                }
                if (!empty($whatsappUrl)) {
                    $contactText .= " or using the direct contact link: {$whatsappUrl}";
                }
                $contactText .= ". Live patient coordinators and support agents are also available during business hours directly through the website chat widget.";

                $chunkItems[] = [
                    'section_title' => "{$orgName} Contact Channels & Coordination",
                    'text' => $contactText,
                ];
            }

            // Index chunks into knowledge_chunks table
            $count = 0;
            foreach ($chunkItems as $index => $item) {
                $text = trim($item['text']);
                if (empty($text)) {
                    continue;
                }

                $embedding = null;
                try {
                    $embedding = $this->embeddings->embed($text);
                } catch (Throwable $e) {
                    Log::info("Embedding fallback for chunk {$index}: " . $e->getMessage());
                }

                KnowledgeChunk::create([
                    'id' => (string) Str::uuid(),
                    'organization_id' => $org->id,
                    'source_id' => $source->id,
                    'section_title' => $item['section_title'],
                    'chunk_text' => $text,
                    'chunk_index' => $index,
                    'embedding' => $embedding,
                    'metadata' => [
                        'character_count' => strlen($text),
                        'source' => 'organization_settings',
                    ],
                ]);
                $count++;
            }

            $source->update([
                'status' => 'indexed',
                'metadata' => array_merge($source->metadata ?? [], [
                    'chunk_count' => $count,
                    'indexed_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::info("Synced {$count} settings knowledge chunks for {$orgName} ({$org->id})");
            return $count;
        } catch (Throwable $e) {
            Log::error("Failed to sync settings knowledge for {$orgName}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clean raw administrative headers so customers see natural language rather than metadata dumps.
     */
    private function cleanRawMetadataHeaders(string $rawText, string $orgName): string
    {
        $text = $rawText;

        // Strip literal developer prompts
        $text = preg_replace('/You are the official AI assistant for [^.]+\./i', '', $text);
        $text = preg_replace('/^Organization Name:\s*.*$/im', '', $text);
        $text = preg_replace('/^Website:\s*.*$/im', '', $text);
        $text = preg_replace('/^Industry:\s*.*$/im', '', $text);
        $text = preg_replace('/^Target Audience:\s*.*$/im', '', $text);
        $text = preg_replace('/^Primary Objective:\s*.*$/im', '', $text);
        $text = preg_replace('/^Communication Languages:\s*.*$/im', '', $text);
        $text = preg_replace('/^Official Information Source:\s*.*$/im', '', $text);
        $text = preg_replace('/^Contact Channel:\s*.*$/im', '', $text);
        $text = preg_replace('/^Organization Description:\s*/im', '', $text);
        $text = preg_replace('/^(ANSWERING|PATIENT SUPPORT|HUMAN HANDOFF|MEDICAL QUESTIONS|PRIVACY|STYLE):\s*/im', '', $text);

        // Compress multiple newlines
        $text = preg_replace("/\n{3,}/", "\n\n", trim($text));

        return $text ?: $rawText;
    }
}
