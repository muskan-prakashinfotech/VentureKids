<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTernsPolicyToAdminOthersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("UPDATE `admin_others` SET `setting_value`='<p>
The Trainer agrees to provide educational services specifically in the area of entrepreneurship education.
</p>

<p>
<span style=\"font-weight: bold;\">1. Scope of Services</span><br />
The Trainer\'s responsibilities include, but are not limited to:
<ul>
	<li>Developing and delivering educational content</li>
	<li>Conducting classes, workshops, or seminars by agreed schedules and formats.</li>
	<li>Assessing students\' performance and providing constructive feedback as required.</li>
</ul>
</p>

<p>
<span style=\"font-weight: bold;\">2. Term</span><br />
This tech platform shall continue until terminated by mutual agreement
</p>

<p>
<span style=\"font-weight: bold;\">3. Confidentiality</span><br />
The Trainer agrees to maintain the confidentiality of all proprietary or confidential information. This includes but is not limited to student records, course materials, and any other information obtained during the engagement.
</p>

<p>
<span style=\"font-weight: bold;\">4. Compliance with Policies</span><br />
The Trainer agrees to comply with all applicable policies and guidelines including but not limited to codes of conduct, safety protocols, and educational standards.
</p>

<p>
<span style=\"font-weight: bold;\">5. Intellectual Property</span><br />
Any intellectual property developed or used by the Trainer in connection with their services shall remain the property of the Trainer unless otherwise agreed in writing
</p>

<p>
<span style=\"font-weight: bold;\">6. Indemnification</span><br />
The Trainer agrees to indemnify and hold harmless the School from any claims, liabilities, damages, or expenses arising out of or related to the Trainer\'s performance or conduct.
</p>

<p>
<span style=\"font-weight: bold;\">7. Changes to This Policy</span><br />
We may update this Terms of Use & Privacy Policy from time to time. We will notify you of any changes by posting the new policy on our Platform and updating the effective date. Continued use of the Platform after any changes indicates acceptance of the new terms.
</p>

<p>
<span style=\"font-weight: bold;\">8. Contact Information</span><br />
If you have any questions about these Terms of Use or our Privacy Policy, please contact us at <a href=\"mailto:enquiry@venderkids.com\">enquiry@venderkids.com</a>.
</p>

<p>
<span style=\"font-weight: bold;\">9. Data Sharing and Disclosure</span><br />
We do not share personal information with third parties except as necessary to provide our services, comply with the law, or protect our rights. Third-party service providers are bound by confidentiality obligations.
</p>

<p>
<span style=\"font-weight: bold;\">10. Data Security</span><br />
We implement reasonable security measures to protect against unauthorized access to or unauthorized alteration, disclosure, or destruction of data. However, no security system is impenetrable, and we cannot guarantee the security of our systems 100%.
</p>

<br />

<p>
<span style=\"font-weight: bold;\">Acknowledgment:</span><br />
By using venderkids, I acknowledge that I have read and agree to these Terms of Use and Privacy Policy.
</p>' WHERE `setting_name`='teacher'");

DB::statement("UPDATE `admin_others` SET `setting_value`='<p>
<span style=\"font-weight: bold;\">1. Information We Collect</span><br />
We collect information that your school provides directly to us, such as user names, email addresses, and educational records. We also collect data automatically as you use our Platform, including usage statistics and device information.
</p>

<p>
<span style=\"font-weight: bold;\">2. How We Use Information</span><br />
The information collected is used to:
<ul>
	<li>Provide and improve our services.</li>
	<li>Communicate with students and schools to provide updates.</li>
	<li>Ensure the security and integrity of our services.</li>
</ul>
</p>

<p>
<span style=\"font-weight: bold;\">3. Data Sharing and Disclosure</span><br />
We do not share personal information with third parties except as necessary to provide our services, comply with the law, or protect our rights. Third-party service providers are bound by confidentiality obligations.
</p>

<p>
<span style=\"font-weight: bold;\">4. Data Security</span><br />
We implement reasonable security measures to protect against unauthorized access to or unauthorized alteration, disclosure, or destruction of data. However, no security system is impenetrable, and we cannot guarantee the security of our systems 100%.
</p>

<p>
<span style=\"font-weight: bold;\">5. Student Data Privacy</span><br />
We comply with all applicable laws and regulations regarding the collection, use, and disclosure of student data. The School retains ownership of all student data, and we only use such data to provide our services.
</p>

<p>
<span style=\"font-weight: bold;\">6. Changes to This Policy</span><br />
We may update this Privacy Policy from time to time. We will notify the School of any changes by posting the new policy on our Platform and updating the effective date. Continued use of the Platform after any changes indicates acceptance of the new terms.
</p>

<p>
<span style=\"font-weight: bold;\">7. Contact Information</span><br />
If you have any questions about these Terms of Use or our Privacy Policy, please contact us at <a href=\"mailto:enquiry@venderkids.com\">enquiry@venderkids.com</a>.
</p>

<br />

<p>
<span style=\"font-weight: bold;\">Acknowledgment:</span><br />
By using venderkids, the Parent/Student acknowledges that they have read and agree to these Terms of Use and Privacy Policy.
</p>
' WHERE `setting_name`='student'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
