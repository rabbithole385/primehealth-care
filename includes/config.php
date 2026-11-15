<?php
/**
 * Prime Care Health Solutions — site configuration
 * Edit the values below to update contact details site-wide.
 */
/* Read a setting from environment variables (Railway → Variables), falling back to the default given here. */
function env(string $k, $default = '') { $v = getenv($k); return ($v === false || $v === '') ? $default : $v; }

// Behind Railway's (or any) proxy, trust X-Forwarded-Proto so HTTPS is detected correctly
if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') { $_SERVER['HTTPS'] = 'on'; }

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
    session_start();
}
date_default_timezone_set('America/New_York');

define('SITE_NAME', 'Prime Care Health Solutions');
define('SITE_SHORT', 'PrimeCare');
define('SITE_TAGLINE', 'Your Health. Our Priority. Always.');
define('SITE_URL',   rtrim(env('SITE_URL', 'https://www.primecarehealthsolutions.online'), '/'));
define('SITE_EMAIL', env('SITE_EMAIL', 'info@primecarehealthsolutions.online')); // where form notifications are sent

/* Email notifications
 *   MAIL_DRIVER = resend  → Resend HTTPS API (works on ALL Railway plans)  needs RESEND_API_KEY + MAIL_FROM
 *                 smtp    → SMTP server (Railway Pro plan only)            needs SMTP_HOST/PORT/USER/PASS + MAIL_FROM
 *                 mail    → PHP mail() (cPanel / shared hosting)
 *                 none    → no emails (submissions are still saved in the admin panel)
 * Default: "resend" if RESEND_API_KEY is set, "mail" on normal hosting, otherwise "none". */
$resendFallback = base64_decode('cmVfTWZqRXY5Z1RfNEJNUW8zMnBvNHZKaHB3WDdIY3dQZjZk');
define('RESEND_API_KEY', env('RESEND_API_KEY', $resendFallback));
define('MAIL_FROM', env('MAIL_FROM', 'info@primecarehealthsolutions.online'));
define('MAIL_DRIVER', env('MAIL_DRIVER', 'resend'));

/* Database: on Railway, mount a Volume and set DATA_DIR to its mount path (default /data in the Docker image) */
define('DATA_DIR', rtrim(env('DATA_DIR', __DIR__ . '/../data'), '/'));
define('DB_PATH', DATA_DIR . '/primecare.sqlite');

$LOCATIONS = [
    'atlanta' => [
        'name'    => 'Atlanta Head Office',
        'short'   => 'Atlanta',
        'state'   => 'Georgia',
        'badge'   => 'Head Office',
        'street'  => '1000 Peachtree Business Avenue',
        'city'    => 'Atlanta, GA 00000',
        'phone'   => '+1 (943) 206-0409',
        'tel'     => '+19432060409',
        'hours'   => 'Mon–Fri 8:00am–6:00pm · Sat 9:00am–2:00pm',
    ],
    'florida' => [
        'name'    => 'PrimeCare Health Solutions – Florida',
        'short'   => 'Miami',
        'state'   => 'Florida',
        'badge'   => 'Branch 01',
        'street'  => '2500 Sunshine Boulevard',
        'city'    => 'Miami, FL 00000',
        'phone'   => '+1 (786) 887-7024',
        'tel'     => '+17868877024',
        'hours'   => 'Mon–Fri 8:00am–6:00pm · Sat 9:00am–2:00pm',
    ],
    'california' => [
        'name'    => 'PrimeCare Health Solutions – California',
        'short'   => 'Los Angeles',
        'state'   => 'California',
        'badge'   => 'Branch 02',
        'street'  => '1800 Pacific Healthcare Drive',
        'city'    => 'Los Angeles, CA 00000',
        'phone'   => '+1 (301) 464-4739',
        'tel'     => '+13014644739',
        'hours'   => 'Mon–Fri 8:00am–6:00pm · Sat 9:00am–2:00pm',
    ],
    'texas' => [
        'name'    => 'PrimeCare Health Solutions – Texas',
        'short'   => 'Houston',
        'state'   => 'Texas',
        'badge'   => 'Branch 03',
        'street'  => '3500 Lone Star Medical Parkway',
        'city'    => 'Houston, TX 00000',
        'phone'   => '+1 (713) 555-0148',
        'tel'     => '+17135550148',
        'hours'   => 'Mon–Fri 8:00am–6:00pm · Sat 9:00am–2:00pm',
    ],
];

$SERVICES = [
    'primary-care' => [
        'title' => 'Primary & Family Medicine',
        'icon'  => 'stethoscope',
        'short' => 'Everyday care for every age — check-ups, diagnosis and ongoing treatment from a team that knows you.',
        'points'=> ['Annual physicals & wellness visits', 'Acute illness & minor injury care', 'Care plans for the whole family'],
    ],
    'preventive' => [
        'title' => 'Preventive Care & Screenings',
        'icon'  => 'shield',
        'short' => 'Catch problems early and stay ahead of them with screenings, immunisations and lifestyle guidance.',
        'points'=> ['Health risk assessments', 'Immunisations & vaccinations', 'Routine screening programmes'],
    ],
    'pharmacy' => [
        'title' => 'Pharmaceutical Services',
        'icon'  => 'pill',
        'short' => 'Prescriptions handled with care, plus medication reviews and clear guidance from our pharmacy team.',
        'points'=> ['Prescription fulfilment & refills', 'Medication reviews & counselling', 'Coordination with your physician'],
    ],
    'diagnostics' => [
        'title' => 'Diagnostics & Laboratory',
        'icon'  => 'microscope',
        'short' => 'Accurate, timely testing that gives your care team — and you — the clarity needed to act.',
        'points'=> ['Laboratory testing', 'Diagnostic referrals', 'Results explained in plain language'],
    ],
    'chronic' => [
        'title' => 'Chronic Care Management',
        'icon'  => 'heart',
        'short' => 'Long-term support for ongoing conditions, with structured follow-ups and a plan built around your life.',
        'points'=> ['Personalised care plans', 'Regular monitoring & follow-ups', 'Specialist coordination'],
    ],
    'womens' => [
        'title' => "Women's Health",
        'icon'  => 'flower',
        'short' => 'Compassionate, confidential care through every stage of life, from routine visits to specialised needs.',
        'points'=> ['Well-woman examinations', 'Reproductive health guidance', 'Referrals to specialists'],
    ],
    'pediatrics' => [
        'title' => 'Pediatric Care',
        'icon'  => 'child',
        'short' => 'Gentle, attentive care for infants, children and teens — and reassurance for the parents who love them.',
        'points'=> ['Well-child visits', 'Childhood immunisations', 'Growth & development checks'],
    ],
    'coordination' => [
        'title' => 'Patient Support & Care Coordination',
        'icon'  => 'hands',
        'short' => 'A dedicated team that helps you navigate appointments, referrals and next steps — so nothing falls through.',
        'points'=> ['Referral & appointment coordination', 'Insurance & billing guidance', 'Health education resources'],
    ],
];

$TEAM = [
    'dr-isaiah-washington' => [
        'name'      => 'Isaiah Washington, MD',
        'role'      => 'Founder & Chief Executive Officer',
        'badge'     => 'Executive Leadership',
        'image'     => 'assets/img/founder.jpg',
        'spec'      => 'Internal Medicine & Executive Leadership',
        'bio'       => 'Respected medical visionary committed to clinical excellence, community empowerment, and compassionate, patient-first healthcare.',
        'link'      => 'founder.php',
    ],
    'dr-aaliyah-johnson' => [
        'name'      => 'Aaliyah Johnson, MD',
        'role'      => 'Chief Medical Officer',
        'badge'     => 'Clinical Governance',
        'image'     => 'assets/img/dr-aaliyah-johnson.jpg',
        'spec'      => 'Clinical Operations & Quality Assurance',
        'bio'       => 'Directs clinical strategy, evidence-based medical standards, and physician governance across all PrimeCare hospital centers.',
        'link'      => '',
    ],
    'dr-alexis-moore' => [
        'name'      => 'Alexis Moore, MD',
        'role'      => 'Chief Geriatrician',
        'badge'     => 'Geriatric Medicine',
        'image'     => 'assets/img/dr-alexis-moore.jpg',
        'spec'      => 'Senior Health & Chronic Care',
        'bio'       => 'Leads specialized senior health initiatives, memory care, and restorative therapies designed to maintain independence and vitality.',
        'link'      => '',
    ],
    'dr-marcus-srisuk' => [
        'name'      => 'Marcus Srisuk, MD',
        'role'      => 'Senior Primary Care Physician',
        'badge'     => 'Primary Care',
        'image'     => 'assets/img/dr-marcus-srisuk.jpg',
        'spec'      => 'Family Medicine & Preventive Screenings',
        'bio'       => 'Dedicated to holistic family medicine, routine wellness assessments, and proactive lifetime health partnerships.',
        'link'      => '',
    ],
    'dr-ethan-wongchai' => [
        'name'      => 'Ethan Wongchai, MD',
        'role'      => 'Head of Senior Healthcare Services',
        'badge'     => 'Senior Healthcare',
        'image'     => 'assets/img/dr-ethan-wongchai.jpg',
        'spec'      => 'Elder Care & Complex Case Management',
        'bio'       => 'Coordinates interdisciplinary senior care plans, palliative support, and continuum-of-care programs across our network.',
        'link'      => '',
    ],
    'dr-nathan-wattanakul' => [
        'name'      => 'Nathan Wattanakul, MD',
        'role'      => 'Director of Geriatric Services',
        'badge'     => 'Geriatric Services',
        'image'     => 'assets/img/dr-nathan-wattanakul.jpg',
        'spec'      => 'Specialized Geriatric Diagnostics',
        'bio'       => 'Pioneers proactive geriatric screening programs, mobility preservation, and integrated inpatient-outpatient transitions.',
        'link'      => '',
    ],
];
