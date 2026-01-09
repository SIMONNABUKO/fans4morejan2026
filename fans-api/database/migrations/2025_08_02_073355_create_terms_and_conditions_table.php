<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('terms_and_conditions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('CREATORS AGREEMENT TO TERMS');
            $table->text('disclaimer');
            $table->text('fan_definition');
            $table->text('agreement_intro');
            $table->text('additional_terms');
            $table->json('terms_list');
            $table->text('europe_regulation');
            $table->text('fan_terms');
            
            // Fees section
            $table->string('fees_title')->default('Fees');
            $table->text('fees_description');
            
            // Account setup section
            $table->string('account_setup_title')->default('Setting up your Creator account');
            $table->json('account_requirements');
            
            // Legal responsibility section
            $table->string('legal_responsibility_title')->default('Personal legal responsibility of Creators');
            $table->text('legal_responsibility_description');
            
            // Transactions section
            $table->string('transactions_title')->default('Fan/Creator Transactions');
            $table->json('transaction_points');
            
            // Content section
            $table->string('content_title')->default('Content – general terms');
            $table->text('content_intro');
            $table->json('content_points');
            
            // Co-authored content section
            $table->string('co_authored_title')->default('Co-authored Content');
            $table->json('co_authored_points');
            
            // Payouts section
            $table->string('payouts_title')->default('Payouts to Creators');
            $table->json('payout_points');
            
            // Withholding section
            $table->string('withholding_title')->default('Circumstances in which we may withhold Creator Earnings');
            $table->json('withholding_points');
            
            // Tax section
            $table->string('tax_title')->default('Promoting Tax compliance and VAT');
            $table->json('tax_points');
            
            // Section 2257
            $table->string('section_2257_title')->default('Section 2257 Disclosure Statement');
            $table->text('section_2257_content_subject');
            $table->text('section_2257_exemption');
            $table->text('section_2257_title_work');
            $table->text('section_2257_records');
            $table->text('section_2257_company');
            
            // Complaints section
            $table->string('complaints_title')->default('COMPLAINTS POLICY');
            $table->text('complaints_introduction');
            $table->text('complaints_contact');
            $table->text('complaints_purpose');
            $table->text('complaints_how_to_complain');
            $table->text('complaints_alternative_contact');
            $table->string('complaints_illegal_content_title')->default('How we will deal with complaints of illegal or non-consensual Content:');
            $table->text('complaints_illegal_content_intro');
            $table->json('complaints_illegal_content_points');
            $table->string('complaints_other_complaints_title')->default('Other Complaints');
            $table->json('complaints_other_complaints_points');
            
            // Metadata
            $table->string('version')->default('1.0');
            $table->string('document_id')->default('TOS-2024-001');
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms_and_conditions');
    }
};
