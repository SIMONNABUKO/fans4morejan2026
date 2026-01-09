<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TermsAndCondition;

class TermsAndConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Backup current terms and conditions from locale files
        $terms = [
            'title' => 'CREATORS AGREEMENT TO TERMS',
            'disclaimer' => 'BY USING OUR FANS4MORE AS A CREATOR YOU AGREE TO THESE TERMS – (PLEASE READ CAREFULLY)',
            'fan_definition' => 'The use of the word "fan" will refer to the follower or subscriber.',
            'agreement_intro' => 'These Creators Agreement To Terms Of Use are additional terms which apply if you use Fans4More as a Creator. These Creator Agreement to Terms of Use form part of your agreement with us.',
            'additional_terms' => 'More terms which will apply to your use of Fans4More:',
            'terms_list' => [
                'terms_conditions' => 'Our Terms and conditions',
                'privacy_policy' => 'Our Privacy Policy',
                'complaints_policy' => 'Our Complaints Policy',
                'contract' => 'Contract between Creator and Fan',
                'other_terms' => 'Other terms which may apply to your use of Fans4More.'
            ],
            'europe_regulation' => 'If you are established or resident in the Europe Regulation Terms will also apply to you',
            'fan_terms' => 'If you are also a Fan, the Terms and conditions for Fans will also apply to your use of Fans4More as a Fan',
            'fees_title' => 'Fees',
            'fees_description' => 'We charge a fee to you of twenty per cent (20%) of all User Payments made to you. The remaining eighty per cent (80%) of the User Payment is payable to you "Creator Earnings". Our Fee includes the costs of providing, maintaining and operating OnlyFans and storing your Content. Creator Earnings are paid to you in the way described in the withdrawal in our terms and conditions.',
            'account_setup_title' => 'Setting up your Creator account',
            'account_requirements' => [
                'You will be required to submit a picture of you holding your ID',
                'You will need on your account a bank account for withdrawals.',
                'You will also be required to submit a valid filled out w9 form for tax purposes.',
                'You may also need to submit additional information depending on the country where you live.',
                'We may ask you for additional age or identity verification information at any time. We may reject your application to set up a Creator account for any reason, including the reasons stated here.',
                'You will then be able to start adding Content and Users will be able to subscribe to your account to become your Fans.',
                'If you lose access to your account, you can reset your password, but you will need to know the email address used to set up the account to do so. If you do not recall the email address used to set up the account, we may require you to provide identification documents and photos and any additional evidence we may reasonably require to prove your identity.'
            ],
            'legal_responsibility_title' => 'Personal legal responsibility of Creators',
            'legal_responsibility_description' => 'Only individuals can be Creators. Every Creator is bound personally by the Terms and conditions. If you have an agent, agency, management company or other third party which assists you with the operation of your Creator account (or operates it on your behalf), this does not affect your personal legal responsibility. Our relationship is with you, and not with any third party.',
            'transactions_title' => 'Fan/Creator Transactions',
            'transaction_points' => [
                'All Fan/Creator Transactions are contracts between Fans and Creators. Although we facilitate Fan/Creator Transactions by providing the Fans4More platform and storing Content, we are not a party to the Contract between Fan and Creator or any other contract which may exist between a Fan and Creator, and are not responsible for any Fan/Creator Transaction.',
                'Fan Payments are exclusive of tax, which shall be added at the current rate as applicable to Fan Payments.',
                'When you receive confirmation from OnlyFans, either in the \'Statements\' page of your User account or by email (or both), that the Fan/Creator Transaction has been confirmed, you must perform your part of such Fan/Creator Transaction. You agree that you will indemnify us for any breach by you of this obligation (which means you will be responsible for any loss or damage (including loss of profit) we suffer as a result of you failing to comply with this obligation).'
            ],
            'content_title' => 'Content – general terms',
            'content_intro' => 'In addition to the terms set out elsewhere in the Terms and conditions, the following terms apply to the Content posted, displayed, uploaded or published by you as a Creator on Fans4More.',
            'content_points' => [
                'Your Content is not confidential, and you authorize your Fans to access and view your Content on Fans4More for their own lawful and personal use, and in accordance with any licenses that you grant to your Fans.',
                'You lawfully agree that for each item of Content which you post, display, upload or publish on Fans4More:',
                'the Content complies in full with the Terms and conditions.',
                'you hold all rights necessary to license and deal in your Content on Fans4More, including in each territory where you have Fans and in the United Kingdom',
                'you either own your Content (and all intellectual property rights in it) or have a valid license to offer and supply your Content to your Fans',
                'if your Content includes or uses any third-party material, you have secured all rights, licenses, written consents and releases that are necessary for the use of such third-party property in your Content and for the subsequent use and exploitation of that Content on Fans4More',
                'the Content is:',
                'of satisfactory quality, taking account of any description of the Content, the price, and all other relevant circumstances including any statement or representation which you make about the nature of the Content on your account.',
                'reasonably suitable for any purpose which the Fan has made known to you is the purpose for which the Fan is using the Content',
                'as described by you.',
                'You agree that you will be responsible for any loss or damage (including loss of profit) we suffer as a result of any of the warranties being false.',
                'We are not responsible for and do not endorse any aspect of any Content posted by you or any other User of Fans4More. We do not have any obligation to monitor any Content and have no direct control over what your Content may comprise.',
                'You also agree to act as custodian of records for the Content that you upload to Fans4More.'
            ],
            'co_authored_title' => 'Co-authored Content',
            'co_authored_points' => [
                'If you upload Content to your Creator account which shows anyone else other than or in addition to you (even if that person cannot be identified from the Content) ("Co-Authored Content"), you legally agree that each individual shown in any Co-Authored Content uploaded to your account is 1) a Creator on Fans4More; or 2) a consenting adult, and that you have verified the identity and age of each such individual and will provide supporting documents as we may request in our discretion.',
                'You further legally agree that you have obtained and keep on record written consent from each individual shown in your Co-Authored Content that such individual:',
                'has given his or her express, prior and fully informed consent to his or her appearance in the Co-Authored Content',
                'has consented to the Co-Authored Content in which he or she appears being posted on Fans4More.',
                'You agree also that if you upload Co-Authored Content where the other person or people appearing in the Content maintain a Creator account on Fans4More, you will tag the Fans4More account(s) of any person or people appearing in the Co-Authored Content who can be identified from it.',
                'If any Co-Authored Content is a work of joint authorship, you are solely responsible for obtaining any required licenses or consents from any other joint authors of the Content which are sufficient to permit such Content to be uploaded to and made available on OnlyFans.',
                'You agree that we will only arrange for Creator Earnings to be paid to the account of the Creator to which the Co-Authored Content is uploaded.',
                'You agree to release us from and not to make any claims against us arising from Co-Authored Content. You agree that all claims arising from Co-Authored Content shall be made against the Creator(s) who posted Co-Authored Content or the individual(s) who appeared in the Co-Authored Content.'
            ],
            'payouts_title' => 'Payouts to Creators',
            'payout_points' => [
                'All Fan Payments will be received by a third-party payment provider ccbill.',
                'JUSTONLYSMANA LLC will collect the Fan Payment and pay the Creator Earnings to your bank.',
                'Your account will be updated within a reasonable time with your Creator Earnings. Your Creator Earnings will become available for withdrawal by you from your account once such Creator Earnings appear in your account.',
                'To make a withdrawal of Creator Earnings from your Fans4More account, you must have at least the minimum payout amount in your Fans4More account.',
                'If a Fan successfully seeks a refund or chargeback from their credit card provider in respect of a Fan Payment made to you, we may investigate and may decide to deduct from your account an amount equal to the Creator Earnings earned by you on the charged-back or refunded amount.',
                'Except for Payout Options involving payment by direct bank transfer, we do not store any data disclosed by you when you register your Payout Options with a third-party payment provider.'
            ],
            'withholding_title' => 'Circumstances in which we may withhold Creator Earnings',
            'withholding_points' => [
                'We may withhold all or any part of the Creator Earnings due to you but not yet paid out:',
                'if we think that you have or may have seriously or repeatedly breached any part of the Terms and conditions',
                'if you attempt or threaten to breach any part of the Terms and conditions in a way which we think has or could have serious consequences for us or another User (including actual or possible loss caused to us or another User)',
                'if we suspect that all or any part of the Creator Earnings result from unlawful or fraudulent activity, either by you or by the Fan who made the Fan Payment resulting in the Creator Earnings',
                'for as long as is necessary to investigate the actual, threatened or suspected breach by you or the suspected unlawful activity (as applicable). If following our investigation, we conclude that (i) you have seriously or repeatedly breached any part of the Terms and conditions; (ii) you have attempted or threatened to breach any part of the Terms and conditions in a way which has or could have serious consequences for us or another User (including actual or possible loss caused to us or another User), and/or (iii) the Creator Earnings result from unlawful or fraudulent activity, we may notify you that you have forfeited your Creator Earnings.',
                'We may also withhold all or any part of the Creator Earnings due to you but not yet paid out if we receive notice that you have secured, encumbered, pledged, assigned, or otherwise allowed a lien to be placed on Creator Earnings. We undertake no duty to pay Creator Earnings to third-party lienholders and may withhold payment of Creator Earnings until the lien has been removed.',
                'We shall not have any responsibility to you if we withhold or forfeit any of your Creator Earnings where we have a right to do so under these Terms and conditions.',
                'If we are withholding all or any part of the Creator Earnings due to you and we determine that part of the Creator Earnings withheld by us is unrelated to breaches by you of the Terms of Service or suspected unlawful or fraudulent activity, then we may arrange for you to be paid the part of the Creator Earnings which we determine to be unrelated to breaches by you of the Terms of Service or suspected unlawful or fraudulent activity. However, you agree that if we consider that your breach(es) of the Terms of Service has or may cause us loss, we may withhold all Creator Earnings due to you but not yet paid and we may set off such amounts against any losses suffered by us.',
                'If once we have finished our investigation we determine that Creator Earnings are forfeited, we will (unless prohibited by law) use our best efforts to ensure that any Fan Payments which resulted in forfeited Creator Earnings are returned to the relevant Fans who paid such Fan Payments.'
            ],
            'tax_title' => 'Promoting Tax compliance and VAT',
            'tax_points' => [
                'We recommend that all Creators seek professional advice to ensure you are compliant with your local Tax, based on your individual circumstances.',
                'By using Fans4More as a Creator, you lawfully agree that you have reported and will report in the future the receipt of all payments made to you in connection with your use of Fans4More to the relevant Tax authority in your jurisdiction, as required by law.'
            ],
            'section_2257_title' => 'Section 2257 Disclosure Statement',
            'section_2257_content_subject' => 'Content Subject to Section 2257: All models, actors, actresses, and other persons that appear in any visual portrayal of actual or simulated sexually explicit conduct appearing on, or otherwise contained on Fans4More were over the age of eighteen (18) years at the time the visual image was produced. Records required for all depictions of actual or simulated sexually explicit conduct as defined by Section 2257 are on file with the appropriate Records Custodian.',
            'section_2257_exemption' => 'Exemption Statement: All other visual depictions displayed on Fans4More are exempt from the provision of Section 2257 because: 1) they do not portray conduct as specifically listed in 18 U.S.C §2256 (2)(A) (i) through (iv); 2) they do not portray conduct as specifically listed in 18 U.S.C. §2257A produced after March 19, 2009; 3) they do not portray conduct listed in 18 U.S.C. §2256(2)(A)(v) produced after March 19, 2009; 4) the visual depictions were created prior to July 3, 1995; or, 5) Fans4More does not act as a "producer" with respect to the dissemination of such exempt images as that term is defined in 28 C.F.R. §75.1(c).',
            'section_2257_title_work' => 'The title of this work is: Fans4More.',
            'section_2257_records' => 'All records associated with depictions contained herein, required to be maintained by United States law, will be made available to authorized inspectors by the Records Custodian at the following location:',
            'section_2257_company' => 'JUSTONLYSMANA LLC\nSAVANNAH, GA 31404',
            'complaints_title' => 'COMPLAINTS POLICY',
            'complaints_introduction' => 'Introduction: This section sets out our complaints policy. If you are a Fans4More User, this Complaints Policy form is part of your agreement with us.',
            'complaints_contact' => 'How to contact us: Fans4More is operated by JUSTONLYS LLC. We are a limited liability company registered in the United States. You can reach us in our Contact section.',
            'complaints_purpose' => 'What is this for? Anyone can use this Complaints Policy to alert us to any complaint which you have relating to any violations mentioned in our terms.',
            'complaints_how_to_complain' => 'How do you make a complaint? If you have a complaint about Fans4More (complaints about Content on Fans4More or the conduct of our Users), please contact us with your complaint at support@fans4more.com including your name, address, contact details, and a description of your complaint, including the URL to the Content to which your complaint is about.',
            'complaints_alternative_contact' => 'If you are unable to contact us by email, we can be reached also by submitting a support ticket. Here Contact',
            'complaints_illegal_content_title' => 'How we will deal with complaints of illegal or non-consensual Content:',
            'complaints_illegal_content_intro' => 'After receipt of your complaint of illegal or non-consensual Content:',
            'complaints_illegal_content_points' => [
                'we will take such steps as we consider to be appropriate to investigate your complaint within a timescale which is appropriate to the nature of your complaint',
                'if we require further information or documents from you, we will contact you to let you know',
                'if we require further information or documents from you, we will contact you to let you know',
                'if we require further information or documents from you, we will contact you to let you know',
                'if we require further information or documents from you, we will contact you to let you know',
                'if we require further information or documents from you, we will contact you to let you know'
            ],
            'complaints_other_complaints_title' => 'Other Complaints',
            'complaints_other_complaints_points' => [
                'If you have any other complaint about Fans4More, please contact us with your complaint at support@fans4more.com including your name, address, contact details, and a description of your complaint.',
                'We will investigate your complaint and respond to you within a reasonable time.',
                'If you are not satisfied with our response, you may escalate your complaint to our management team.',
                'We will keep a record of all complaints received and our responses to them.'
            ],
            'version' => '1.0',
            'document_id' => 'TOS-2024-001',
            'is_active' => true,
            'published_at' => now(),
        ];

        TermsAndCondition::create($terms);
    }
}
