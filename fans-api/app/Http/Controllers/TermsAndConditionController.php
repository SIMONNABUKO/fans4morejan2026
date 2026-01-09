<?php

namespace App\Http\Controllers;

use App\Models\TermsAndCondition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TermsAndConditionController extends Controller
{
    /**
     * Get the latest active terms and conditions.
     */
    public function getLatest(): JsonResponse
    {
        try {
            $terms = TermsAndCondition::getLatest();
            
            if (!$terms) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active terms and conditions found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $terms
            ]);
        } catch (\Exception $e) {
            // Handle database table not existing or other database errors
            return response()->json([
                'success' => false,
                'message' => 'Terms and conditions service is being set up. Please try again in a few minutes.',
                'error' => $e->getMessage()
            ], 503);
        }
    }

    /**
     * Get all terms and conditions (admin only).
     */
    public function index(): JsonResponse
    {
        $terms = TermsAndCondition::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $terms
        ]);
    }

    /**
     * Get a specific terms and conditions by ID.
     */
    public function show($id): JsonResponse
    {
        $terms = TermsAndCondition::find($id);

        if (!$terms) {
            return response()->json([
                'success' => false,
                'message' => 'Terms and conditions not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $terms
        ]);
    }

    /**
     * Create new terms and conditions (admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'disclaimer' => 'required|string',
            'fan_definition' => 'required|string',
            'agreement_intro' => 'required|string',
            'additional_terms' => 'required|string',
            'terms_list' => 'required|array',
            'europe_regulation' => 'required|string',
            'fan_terms' => 'required|string',
            'fees_title' => 'required|string|max:255',
            'fees_description' => 'required|string',
            'account_setup_title' => 'required|string|max:255',
            'account_requirements' => 'required|array',
            'legal_responsibility_title' => 'required|string|max:255',
            'legal_responsibility_description' => 'required|string',
            'transactions_title' => 'required|string|max:255',
            'transaction_points' => 'required|array',
            'content_title' => 'required|string|max:255',
            'content_intro' => 'required|string',
            'content_points' => 'required|array',
            'co_authored_title' => 'required|string|max:255',
            'co_authored_points' => 'required|array',
            'payouts_title' => 'required|string|max:255',
            'payout_points' => 'required|array',
            'withholding_title' => 'required|string|max:255',
            'withholding_points' => 'required|array',
            'tax_title' => 'required|string|max:255',
            'tax_points' => 'required|array',
            'section_2257_title' => 'required|string|max:255',
            'section_2257_content_subject' => 'required|string',
            'section_2257_exemption' => 'required|string',
            'section_2257_title_work' => 'required|string',
            'section_2257_records' => 'required|string',
            'section_2257_company' => 'required|string',
            'complaints_title' => 'required|string|max:255',
            'complaints_introduction' => 'required|string',
            'complaints_contact' => 'required|string',
            'complaints_purpose' => 'required|string',
            'complaints_how_to_complain' => 'required|string',
            'complaints_alternative_contact' => 'required|string',
            'complaints_illegal_content_title' => 'required|string|max:255',
            'complaints_illegal_content_intro' => 'required|string',
            'complaints_illegal_content_points' => 'required|array',
            'complaints_other_complaints_title' => 'required|string|max:255',
            'complaints_other_complaints_points' => 'required|array',
            'version' => 'required|string|max:50',
            'document_id' => 'required|string|max:100',
            'is_active' => 'boolean',
            'published_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Deactivate all existing terms
        TermsAndCondition::where('is_active', true)->update(['is_active' => false]);

        $terms = TermsAndCondition::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Terms and conditions created successfully',
            'data' => $terms
        ], 201);
    }

    /**
     * Update terms and conditions (admin only).
     */
    public function update(Request $request, $id): JsonResponse
    {
        $terms = TermsAndCondition::find($id);

        if (!$terms) {
            return response()->json([
                'success' => false,
                'message' => 'Terms and conditions not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'disclaimer' => 'sometimes|string',
            'fan_definition' => 'sometimes|string',
            'agreement_intro' => 'sometimes|string',
            'additional_terms' => 'sometimes|string',
            'terms_list' => 'sometimes|array',
            'europe_regulation' => 'sometimes|string',
            'fan_terms' => 'sometimes|string',
            'fees_title' => 'sometimes|string|max:255',
            'fees_description' => 'sometimes|string',
            'account_setup_title' => 'sometimes|string|max:255',
            'account_requirements' => 'sometimes|array',
            'legal_responsibility_title' => 'sometimes|string|max:255',
            'legal_responsibility_description' => 'sometimes|string',
            'transactions_title' => 'sometimes|string|max:255',
            'transaction_points' => 'sometimes|array',
            'content_title' => 'sometimes|string|max:255',
            'content_intro' => 'sometimes|string',
            'content_points' => 'sometimes|array',
            'co_authored_title' => 'sometimes|string|max:255',
            'co_authored_points' => 'sometimes|array',
            'payouts_title' => 'sometimes|string|max:255',
            'payout_points' => 'sometimes|array',
            'withholding_title' => 'sometimes|string|max:255',
            'withholding_points' => 'sometimes|array',
            'tax_title' => 'sometimes|string|max:255',
            'tax_points' => 'sometimes|array',
            'section_2257_title' => 'sometimes|string|max:255',
            'section_2257_content_subject' => 'sometimes|string',
            'section_2257_exemption' => 'sometimes|string',
            'section_2257_title_work' => 'sometimes|string',
            'section_2257_records' => 'sometimes|string',
            'section_2257_company' => 'sometimes|string',
            'complaints_title' => 'sometimes|string|max:255',
            'complaints_introduction' => 'sometimes|string',
            'complaints_contact' => 'sometimes|string',
            'complaints_purpose' => 'sometimes|string',
            'complaints_how_to_complain' => 'sometimes|string',
            'complaints_alternative_contact' => 'sometimes|string',
            'complaints_illegal_content_title' => 'sometimes|string|max:255',
            'complaints_illegal_content_intro' => 'sometimes|string',
            'complaints_illegal_content_points' => 'sometimes|array',
            'complaints_other_complaints_title' => 'sometimes|string|max:255',
            'complaints_other_complaints_points' => 'sometimes|array',
            'version' => 'sometimes|string|max:50',
            'document_id' => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean',
            'published_at' => 'sometimes|nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $terms->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Terms and conditions updated successfully',
            'data' => $terms
        ]);
    }

    /**
     * Delete terms and conditions (admin only).
     */
    public function destroy($id): JsonResponse
    {
        $terms = TermsAndCondition::find($id);

        if (!$terms) {
            return response()->json([
                'success' => false,
                'message' => 'Terms and conditions not found'
            ], 404);
        }

        $terms->delete();

        return response()->json([
            'success' => true,
            'message' => 'Terms and conditions deleted successfully'
        ]);
    }

    /**
     * Publish terms and conditions (admin only).
     */
    public function publish($id): JsonResponse
    {
        $terms = TermsAndCondition::find($id);

        if (!$terms) {
            return response()->json([
                'success' => false,
                'message' => 'Terms and conditions not found'
            ], 404);
        }

        // Deactivate all existing terms
        TermsAndCondition::where('is_active', true)->update(['is_active' => false]);

        // Activate and publish the selected terms
        $terms->update([
            'is_active' => true,
            'published_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terms and conditions published successfully',
            'data' => $terms
        ]);
    }
}
