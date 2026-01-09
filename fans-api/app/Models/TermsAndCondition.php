<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TermsAndCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'disclaimer',
        'fan_definition',
        'agreement_intro',
        'additional_terms',
        'terms_list',
        'europe_regulation',
        'fan_terms',
        'fees_title',
        'fees_description',
        'account_setup_title',
        'account_requirements',
        'legal_responsibility_title',
        'legal_responsibility_description',
        'transactions_title',
        'transaction_points',
        'content_title',
        'content_intro',
        'content_points',
        'co_authored_title',
        'co_authored_points',
        'payouts_title',
        'payout_points',
        'withholding_title',
        'withholding_points',
        'tax_title',
        'tax_points',
        'section_2257_title',
        'section_2257_content_subject',
        'section_2257_exemption',
        'section_2257_title_work',
        'section_2257_records',
        'section_2257_company',
        'complaints_title',
        'complaints_introduction',
        'complaints_contact',
        'complaints_purpose',
        'complaints_how_to_complain',
        'complaints_alternative_contact',
        'complaints_illegal_content_title',
        'complaints_illegal_content_intro',
        'complaints_illegal_content_points',
        'complaints_other_complaints_title',
        'complaints_other_complaints_points',
        'version',
        'document_id',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'terms_list' => 'array',
        'account_requirements' => 'array',
        'transaction_points' => 'array',
        'content_points' => 'array',
        'co_authored_points' => 'array',
        'payout_points' => 'array',
        'withholding_points' => 'array',
        'tax_points' => 'array',
        'complaints_illegal_content_points' => 'array',
        'complaints_other_complaints_points' => 'array',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Scope a query to only include active terms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include published terms.
     */
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * Get the latest active terms.
     */
    public static function getLatest()
    {
        return static::active()->published()->latest('published_at')->first();
    }
}
