<?php

namespace Database\Seeders;

use App\Models\PrivacyPolicy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrivacyPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $privacyPolicyContent = <<<HTML
<h1 class="text-3xl font-bold mb-6">Privacy Policy</h1>

<p class="mb-4 text-gray-600 dark:text-gray-300">Last updated: August 4, 2025</p>

<div class="space-y-6">
    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">1. Introduction</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            Welcome to Fans4More ("we," "our," or "us"). We are committed to protecting your privacy and ensuring the security of your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our platform and services.
        </p>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            By using Fans4More, you agree to the collection and use of information in accordance with this policy. If you do not agree with our policies and practices, please do not use our service.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">2. Information We Collect</h2>
        
        <h3 class="text-xl font-medium mb-3 text-gray-800 dark:text-gray-200">2.1 Personal Information</h3>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We may collect personal information that you provide directly to us, including:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Name, email address, and username</li>
            <li>Profile information and bio</li>
            <li>Payment and billing information</li>
            <li>Content you create, upload, or share</li>
            <li>Communications with other users</li>
            <li>Preferences and settings</li>
        </ul>

        <h3 class="text-xl font-medium mb-3 text-gray-800 dark:text-gray-200">2.2 Automatically Collected Information</h3>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We automatically collect certain information when you use our service:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Device information (IP address, browser type, operating system)</li>
            <li>Usage data (pages visited, time spent, interactions)</li>
            <li>Location data (if you grant permission)</li>
            <li>Cookies and similar tracking technologies</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">3. How We Use Your Information</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We use the collected information for various purposes:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Provide, maintain, and improve our services</li>
            <li>Process payments and transactions</li>
            <li>Send notifications and updates</li>
            <li>Personalize your experience</li>
            <li>Ensure platform security and prevent fraud</li>
            <li>Comply with legal obligations</li>
            <li>Analyze usage patterns and trends</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">4. Information Sharing and Disclosure</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We do not sell, trade, or rent your personal information to third parties. However, we may share your information in the following circumstances:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li><strong>With your consent:</strong> When you explicitly agree to share information</li>
            <li><strong>Service providers:</strong> Trusted third parties who assist in operating our platform</li>
            <li><strong>Legal requirements:</strong> When required by law or to protect our rights</li>
            <li><strong>Business transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
            <li><strong>Safety and security:</strong> To protect users and prevent fraud</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">5. Data Security</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We implement appropriate technical and organizational measures to protect your personal information:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Encryption of data in transit and at rest</li>
            <li>Regular security assessments and updates</li>
            <li>Access controls and authentication measures</li>
            <li>Secure payment processing</li>
            <li>Regular backups and disaster recovery</li>
        </ul>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            However, no method of transmission over the internet is 100% secure, and we cannot guarantee absolute security.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">6. Your Rights and Choices</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            You have certain rights regarding your personal information:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li><strong>Access:</strong> Request a copy of your personal data</li>
            <li><strong>Correction:</strong> Update or correct inaccurate information</li>
            <li><strong>Deletion:</strong> Request deletion of your personal data</li>
            <li><strong>Portability:</strong> Request transfer of your data to another service</li>
            <li><strong>Objection:</strong> Object to certain processing activities</li>
            <li><strong>Withdrawal:</strong> Withdraw consent at any time</li>
        </ul>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">7. Cookies and Tracking Technologies</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We use cookies and similar technologies to enhance your experience:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li><strong>Essential cookies:</strong> Required for basic functionality</li>
            <li><strong>Analytics cookies:</strong> Help us understand usage patterns</li>
            <li><strong>Preference cookies:</strong> Remember your settings and choices</li>
            <li><strong>Marketing cookies:</strong> Provide relevant content and ads</li>
        </ul>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            You can control cookie settings through your browser preferences.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">8. Third-Party Services</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            Our platform may contain links to third-party services or integrate with external providers:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Payment processors (Stripe, PayPal)</li>
            <li>Analytics services (Google Analytics)</li>
            <li>Social media platforms</li>
            <li>Cloud storage providers</li>
        </ul>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            These third parties have their own privacy policies, and we are not responsible for their practices.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">9. Data Retention</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We retain your personal information for as long as necessary to:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Provide our services to you</li>
            <li>Comply with legal obligations</li>
            <li>Resolve disputes and enforce agreements</li>
            <li>Maintain security and prevent fraud</li>
        </ul>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            When you delete your account, we will delete or anonymize your personal data within 30 days, except where retention is required by law.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">10. International Data Transfers</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            Your information may be transferred to and processed in countries other than your own. We ensure appropriate safeguards are in place to protect your data during international transfers.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">11. Children's Privacy</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            Our service is not intended for children under 18 years of age. We do not knowingly collect personal information from children under 18. If you believe we have collected information from a child under 18, please contact us immediately.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">12. Changes to This Privacy Policy</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            We may update this Privacy Policy from time to time. We will notify you of any changes by:
        </p>
        <ul class="list-disc list-inside mb-4 text-gray-700 dark:text-gray-300 space-y-2">
            <li>Posting the new Privacy Policy on this page</li>
            <li>Sending you an email notification</li>
            <li>Displaying a notice on our platform</li>
        </ul>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            Your continued use of our service after any changes constitutes acceptance of the updated policy.
        </p>
    </section>

    <section>
        <h2 class="text-2xl font-semibold mb-4 text-gray-900 dark:text-white">13. Contact Us</h2>
        <p class="mb-4 text-gray-700 dark:text-gray-300">
            If you have any questions about this Privacy Policy or our data practices, please contact us:
        </p>
        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
            <p class="text-gray-700 dark:text-gray-300">
                <strong>Email:</strong> privacy@fans4more.com<br>
                <strong>Address:</strong> [Your Business Address]<br>
                <strong>Phone:</strong> [Your Contact Number]
            </p>
        </div>
    </section>
</div>
HTML;

        PrivacyPolicy::create([
            'content' => $privacyPolicyContent,
            'version' => '1.0',
            'is_active' => true,
            'effective_date' => now(),
        ]);
    }
}
