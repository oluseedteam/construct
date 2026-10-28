<?php

/*
|--------------------------------------------------------------------------
| R Square Construction: content & setting defaults
|--------------------------------------------------------------------------
| These values are shown until an administrator overrides them in /admin.
| `content_schema` is the single source of truth for editable website copy:
| it supplies the default text AND drives the admin "Website Content" form.
| Statistics, testimonials, projects, jobs and highlights live in the database.
*/

return [

    // Optional comma-separated list of extra recipients for admin notification emails.
    'notify_emails' => array_filter(array_map('trim', explode(',', (string) env('NOTIFY_EMAILS', '')))),

    'settings' => [
        'company_name' => 'R Square Construction',
        'tagline' => 'Build • Renovate • Improve',
        'email' => 'rsquareconstruction@gmail.com',
        'phone' => '437-241-9877',
        'whatsapp' => '',
        'address' => 'Ontario, Canada',
        'logo' => '',
        'facebook' => '',
        'instagram' => '',
        'linkedin' => '',
        'x_twitter' => '',
        'youtube' => '',
        'tiktok' => '',
        'seo_title' => 'R Square Construction | Building Better Spaces for a Brighter Tomorrow',
        'seo_description' => 'R Square Construction delivers quality construction and renovation services in Ontario, Canada, tailored to your needs, from concept to completion.',
        'seo_image' => '',
    ],

    /*
     * tab => [ key => [label, type (text|textarea|image), max length, default] ]
     */
    'content_schema' => [

        'Home' => [
            'home.hero_eyebrow' => ['Hero: small heading', 'text', 80, 'Build • Renovate • Improve'],
            'home.hero_title' => ['Hero headline, line 1 (navy)', 'text', 60, 'Building Better Spaces'],
            'home.hero_title_accent' => ['Hero headline, line 2 (accent colour)', 'text', 60, 'for a Brighter Tomorrow'],
            'home.hero_text' => ['Hero description', 'textarea', 500, 'At R Square Construction, we deliver quality construction and renovation services tailored to your needs. From concept to completion, we bring your vision to life with expertise, reliability and exceptional workmanship.'],
            'home.hero_btn_primary' => ['Hero: primary button label', 'text', 30, 'Get a Quote'],
            'home.hero_btn_secondary' => ['Hero: second button label', 'text', 30, 'Explore Our Projects'],
            'home.about_eyebrow' => ['About section: small heading', 'text', 80, 'Our Approach'],
            'home.about_title' => ['About section: title (navy)', 'text', 80, 'Ideas Today.'],
            'home.about_title_accent' => ['About section: title (accent colour)', 'text', 80, 'Landmarks Tomorrow.'],
            'home.about_text' => ['About section: description', 'textarea', 600, 'We combine innovation, expertise, and integrity to deliver construction and renovation solutions that last for generations.'],
            'home.about_btn' => ['About section: button label', 'text', 30, 'Learn More'],
            'home.projects_eyebrow' => ['Featured projects: small heading', 'text', 60, 'Featured Projects'],
            'home.projects_title' => ['Featured projects: title (navy)', 'text', 80, 'Modern Spaces.'],
            'home.projects_accent' => ['Featured projects: title (accent colour)', 'text', 80, 'Real Impact.'],
            'home.projects_btn' => ['Featured projects: button label', 'text', 30, 'View All Projects'],
            'home.testimonials_eyebrow' => ['Testimonials: small heading', 'text', 60, 'Testimonials'],
            'home.testimonials_title' => ['Testimonials: title (white)', 'text', 60, 'What Our'],
            'home.testimonials_accent' => ['Testimonials: title (accent colour)', 'text', 60, 'Clients Say'],
            'home.cta_title' => ['Call to action: title', 'text', 120, "Let's Build Something Extraordinary."],
            'home.cta_text' => ['Call to action: text', 'textarea', 300, 'Tell us about your project and our team will get back to you with a clear, detailed quotation.'],
            'home.cta_btn' => ['Call to action: button label', 'text', 30, 'Request a Quote'],
        ],

        'About' => [
            'about.hero_title' => ['Banner title', 'text', 80, 'About R Square Construction'],
            'about.hero_subtitle' => ['Banner subtitle', 'text', 160, 'Ideas today. Landmarks tomorrow.'],
            'about.story_eyebrow' => ['Story: small heading', 'text', 60, 'Our Story'],
            'about.story' => ['Company story (separate paragraphs with a blank line)', 'textarea', 5000, "R Square Construction provides quality construction and renovation services tailored to your needs, from custom homes and additions to commercial projects and ongoing maintenance.\n\nFrom concept to completion, we bring your vision to life with expertise, reliability and exceptional workmanship. Our promise is simple: Build. Renovate. Improve."],
            'about.mission_title' => ['Mission: heading', 'text', 40, 'Our Mission'],
            'about.mission' => ['Mission text', 'textarea', 800, 'To deliver quality construction and renovation services that bring our clients\' visions to life and build stronger communities.'],
            'about.vision_title' => ['Vision: heading', 'text', 40, 'Our Vision'],
            'about.vision' => ['Vision text', 'textarea', 800, 'To be a trusted name in construction, known for reliable service, exceptional workmanship and spaces that last for generations.'],
        ],

        'Services & Projects' => [
            'services.hero_title' => ['Services banner: title', 'text', 80, 'Our Services'],
            'services.hero_subtitle' => ['Services banner: subtitle', 'text', 160, 'Quality construction and renovation services, from concept to completion.'],
            'services.learn_more' => ['Services list: link label', 'text', 30, 'Learn more'],
            'services.cta_title' => ['Service page side box: title', 'text', 80, 'Interested in this service?'],
            'services.cta_text' => ['Service page side box: text', 'textarea', 200, "Tell us about your project and we'll send you a detailed quotation."],
            'projects.hero_title' => ['Projects banner: title', 'text', 80, 'Our Projects'],
            'projects.hero_subtitle' => ['Projects banner: subtitle', 'text', 160, 'Modern spaces. Real impact.'],
            'projects.cta_title' => ['Project page side box: title', 'text', 80, 'Planning something similar?'],
            'projects.cta_text' => ['Project page side box: text', 'textarea', 200, "Share your plans and we'll prepare a quotation."],
            'gallery.hero_title' => ['Gallery banner: title', 'text', 80, 'Project Gallery'],
            'gallery.hero_subtitle' => ['Gallery banner: subtitle', 'text', 160, 'A look at our sites, our work and our progress.'],
        ],

        'Why Choose Us' => [
            'why.hero_title' => ['Banner title', 'text', 80, 'Why Choose R Square'],
            'why.hero_subtitle' => ['Banner subtitle', 'text', 160, 'Quality workmanship, reliable service and client satisfaction.'],
            'why.eyebrow' => ['Homepage section: small heading', 'text', 60, 'Why Choose Us'],
            'why.title' => ['Homepage section: title (navy)', 'text', 80, 'Built on Trust,'],
            'why.accent' => ['Homepage section: title (accent colour)', 'text', 80, 'Delivered with Care'],
            'why.text' => ['Homepage section: description', 'textarea', 300, 'The commitments that guide every project we take on.'],
        ],

        'Contact & Quote' => [
            'contact.hero_title' => ['Contact banner: title', 'text', 80, 'Contact Us'],
            'contact.hero_subtitle' => ['Contact banner: subtitle', 'text', 160, "Tell us about your project. We'd love to hear from you."],
            'contact.intro_eyebrow' => ['Contact intro: small heading', 'text', 60, 'Get in touch'],
            'contact.intro_title' => ['Contact intro: title (navy)', 'text', 80, "Let's talk about"],
            'contact.intro_accent' => ['Contact intro: title (accent colour)', 'text', 80, 'your project'],
            'contact.intro_text' => ['Contact intro: text', 'textarea', 300, 'Send us a message and a member of our team will respond as soon as possible.'],
            'contact.form_title' => ['Contact form: heading', 'text', 60, 'Send us a message'],
            'contact.success' => ['Contact form: success message', 'textarea', 200, 'Thank you! Your message has been sent and our team will get back to you soon.'],
            'quote.hero_title' => ['Quote banner: title', 'text', 80, 'Request a Quote'],
            'quote.hero_subtitle' => ['Quote banner: subtitle', 'text', 160, "Tell us about your project and we'll prepare a detailed quotation."],
            'quote.success_title' => ['Quote success: heading', 'text', 60, 'Request received'],
            'quote.success_text' => ['Quote success: text', 'textarea', 200, 'Thank you. Our team will review your project and get back to you shortly.'],
        ],

        'Careers' => [
            'careers.hero_title' => ['Careers banner: title', 'text', 80, 'Careers'],
            'careers.hero_subtitle' => ['Careers banner: subtitle', 'text', 160, 'Join the team that builds better spaces.'],
            'careers.intro_title' => ['Intro: title', 'text', 100, 'Build your career with us'],
            'careers.intro_text' => ['Intro: text', 'textarea', 600, 'We are always interested in skilled, reliable people who take pride in their work. Browse our current openings or send us a general application.'],
            'careers.empty_text' => ['Text shown when there are no open positions', 'textarea', 300, 'There are no open positions right now, but we would still love to hear from you. Send us a general application and we will keep it on file.'],
            'careers.form_title' => ['Application form: heading', 'text', 60, 'Apply now'],
            'careers.success_title' => ['Application success: heading', 'text', 60, 'Application received'],
            'careers.success_text' => ['Application success: text', 'textarea', 300, 'Thank you for your interest in joining us. Our team will review your application and contact you if there is a fit.'],
        ],

        'Footer' => [
            'footer.about' => ['Footer description', 'textarea', 400, 'At R Square Construction, we deliver quality construction and renovation services tailored to your needs, from concept to completion.'],
            'footer.copyright' => ['Copyright line (leave blank for the automatic one)', 'text', 190, ''],
        ],

        'Images' => [
            'home.hero_image' => ['Homepage hero background', 'image', 0, 'images/hero.jpg'],
            'home.about_image' => ['Homepage about image', 'image', 0, 'images/demo/flyer-house.jpg'],
            'home.cta_image' => ['Call-to-action background', 'image', 0, 'images/demo/demo-4.jpg'],
            'site.banner_image' => ['Inner-page banner background', 'image', 0, 'images/hero-1280.jpg'],
        ],
    ],
];
