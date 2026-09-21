<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
	use HasFactory;

	protected $table = 'products';

	protected $fillable = [
		'slug',
		'name',
		'category',
		'platform',
		'product_type',
		'short_description',
		'description',
		'icon',
		'image',
		'price',
		'currency',
		'version',
		'compatibility',
		'features',
		'documentation_url',
		'changelog_url',
		'download_type',
		'license_type',
		'status',
		'featured',
		'best_seller',
	];

	protected function casts(): array
	{
		return [
			'price' => 'decimal:2',
			'features' => 'array',
			'featured' => 'boolean',
			'best_seller' => 'boolean',
			'created_at' => 'datetime',
			'updated_at' => 'datetime',
		];
	}
}
