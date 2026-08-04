<?php

return [
    // Section title
    'title' => 'الفوترة الإلكترونية (هيئة الزكاة والضريبة)',
    'section_header' => 'إعدادات الفوترة الإلكترونية للمملكة العربية السعودية',

    // Main toggle
    'enable' => 'تفعيل الفوترة الإلكترونية (هيئة الزكاة)',
    'enable_help' => 'تفعيل الامتثال للفوترة الإلكترونية لهيئة الزكاة والضريبة (السعودية) للفروع المحددة. عند التعطيل، يبقى سير الفواتير الحالي دون تغيير.',

    // Environment settings
    'environment' => 'البيئة',
    'environment_help' => 'اختر بيئة واجهة برمجة التطبيقات للاختبار أو الإنتاج',
    'sandbox' => 'البيئة التجريبية (التطوير)',
    'simulation' => 'بيئة المحاكاة (اختبار ما قبل الإنتاج)',
    'production' => 'الإنتاج (مباشر)',

    // Seller information
    'seller_vat' => 'الرقم الضريبي للبائع',
    'seller_vat_help' => 'رقم التسجيل الضريبي المكون من 15 رقماً المسجل لدى هيئة الزكاة',
    'seller_vat_placeholder' => 'مثال: 300000000000003',
    'seller_name' => 'الاسم القانوني للبائع',
    'seller_name_help' => 'الاسم التجاري القانوني كما هو مسجل لدى هيئة الزكاة',
    'seller_name_placeholder' => 'مثال: شركتك ذ.م.م',

    // Invoice type
    'invoice_type' => 'نوع الفاتورة',
    'invoice_type_help' => 'اختر نوع الفاتورة الافتراضي المستخدم عند إصدار فواتير زاتكا.',
    'invoice_type_simplified' => 'فاتورة مبسطة',
    'invoice_type_standard' => 'فاتورة ضريبية',
    'invoice_type_b2c' => 'B2C فقط (فواتير مبسطة)',
    'invoice_type_b2b' => 'B2B فقط (فواتير ضريبية)',
    'invoice_type_both' => 'كلاهما B2C و B2B',

    // Store selection
    'enabled_stores' => 'معرفات الفروع المفعلة',
    'enabled_stores_help' => 'مصفوفة JSON لمعرفات الفروع حيث تنطبق الفوترة الإلكترونية (اتركه فارغاً لجميع الفروع)',
    'enabled_stores_placeholder' => '[1, 3, 5]',
    'enabled_stores_example' => 'مثال: [1, 3, 5] يفعّل الفوترة الإلكترونية للفروع ذات المعرف 1 و 3 و 5 فقط',

    // Actions
    'test_connection' => 'اختبار الاتصال',
    'test_connection_help' => 'التحقق من الاتصال بواجهة برمجة تطبيقات هيئة الزكاة (متاح في المرحلة 3)',
    'save' => 'حفظ إعدادات الفوترة الإلكترونية',

    // Messages
    'saved_success' => 'تم حفظ إعدادات الفوترة الإلكترونية بنجاح',
    'saved_error' => 'فشل حفظ إعدادات الفوترة الإلكترونية',
    'disabled_notice' => 'الفوترة الإلكترونية معطلة حالياً. فعّل خانة الاختيار أعلاه لتكوين إعدادات هيئة الزكاة.',

    // Validation messages
    'invalid_vat' => 'تنسيق الرقم الضريبي غير صحيح. يجب أن يكون 15 رقماً.',
    'invalid_store_ids' => 'تنسيق معرفات الفروع غير صحيح. يجب أن يكون مصفوفة JSON صالحة من الأرقام.',
    'invalid_environment' => 'البيئة المحددة غير صحيحة.',
    'invalid_invoice_type' => 'نوع الفاتورة المحدد غير صالح.',

    // Phase 3: Onboarding
    'setup_wizard' => 'معالج إعداد هيئة الزكاة',
    'onboarding_title' => 'تهيئة هيئة الزكاة',
    'onboarding_intro' => 'أكمل الخطوات التالية لتفعيل الفوترة الإلكترونية لفرعك. هذه عملية إعداد لمرة واحدة.',
    'onboarding_status' => 'حالة التهيئة',
    'certificate_status' => 'حالة الشهادة',
    'compliance_status' => 'حالة الامتثال',
    'feature_disabled' => 'الفوترة الإلكترونية معطلة. يرجى تفعيلها في الإعدادات أولاً.',

    // Step 1: Generate CSR
    'step_1_title' => 'الخطوة 1: إنشاء طلب توقيع الشهادة',
    'step_1_desc' => 'إنشاء طلب توقيع الشهادة (CSR) والمفتاح الخاص لفرعك.',
    'step_1_button' => 'إنشاء CSR',
    'step_1_status_pending' => 'لم يبدأ',
    'step_1_status_complete' => 'تم إنشاء CSR',
    'onboarding_csr_generated' => 'تم إنشاء CSR بنجاح!',
    'onboarding_csr_failed' => 'فشل في إنشاء CSR',
    'onboarding_missing_settings' => 'يرجى تكوين الرقم الضريبي واسم البائع القانوني في بيانات الفرع الحالي أولاً.',
    'seller_profile_store_notice' => 'أصبحت بيانات البائع (الرقم الضريبي والاسم القانوني) تُدار لكل فرع. حدّث هذه الحقول من نموذج الفرع الحالي.',

    // Step 2: Compliance CSID
    'step_2_title' => 'الخطوة 2: الحصول على CSID الامتثال',
    'step_2_desc' => 'طلب معرّف الختم التشفيري للامتثال (CSID) من هيئة الزكاة باستخدام OTP الخاص بك.',
    'step_2_button' => 'طلب CSID الامتثال',
    'step_2_status_pending' => 'في انتظار الخطوة 1',
    'step_2_status_complete' => 'تم الحصول على CSID الامتثال',
    'step_2_otp_label' => 'رمز OTP (من بوابة هيئة الزكاة)',
    'step_2_otp_placeholder' => 'أدخل رمز OTP المكون من 6 أرقام',
    'onboarding_otp_required' => 'رمز OTP مطلوب',
    'onboarding_csr_not_found' => 'CSR غير موجود. يرجى إنشاء CSR أولاً (الخطوة 1).',
    'onboarding_compliance_csid_obtained' => 'تم الحصول على CSID الامتثال بنجاح!',
    'onboarding_compliance_csid_failed' => 'فشل في الحصول على CSID الامتثال',
    'onboarding_compliance_csid_not_found' => 'CSID الامتثال غير موجود. يرجى إكمال الخطوة 2 أولاً.',

    // Step 3: Compliance Checks
    'step_3_title' => 'الخطوة 3: إجراء فحوصات الامتثال',
    'step_3_desc' => 'إرسال فواتير نموذجية إلى هيئة الزكاة للتحقق من الصحة. يجب أن تنجح جميع الفحوصات قبل البدء.',
    'step_3_button' => 'إجراء فحوصات الامتثال',
    'step_3_status_pending' => 'في انتظار الخطوة 2',
    'step_3_status_complete' => 'نجحت جميع الفحوصات',
    'step_3_status_failed' => 'فشلت بعض الفحوصات',
    'onboarding_compliance_checks_passed' => 'نجحت جميع فحوصات الامتثال!',
    'onboarding_compliance_checks_failed' => 'فشلت بعض فحوصات الامتثال. راجع النتائج أدناه.',
    'onboarding_compliance_checks_error' => 'خطأ في تشغيل فحوصات الامتثال',
    'onboarding_compliance_required' => 'يجب عليك اجتياز فحوصات الامتثال قبل طلب CSID الإنتاج.',

    // Step 4: Production CSID
    'step_4_title' => 'الخطوة 4: الحصول على CSID الإنتاج',
    'step_4_desc' => 'تبديل CSID الامتثال الخاص بك بـ CSID الإنتاج. بعد ذلك، يمكنك البدء في إصدار الفواتير المباشرة.',
    'step_4_button' => 'طلب CSID الإنتاج',
    'step_4_status_pending' => 'في انتظار الخطوة 3',
    'step_4_status_complete' => 'تم الحصول على CSID الإنتاج - جاهز للبدء!',
    'onboarding_production_csid_obtained' => 'تم الحصول على CSID الإنتاج بنجاح! يمكنك الآن إصدار فواتير متوافقة مع هيئة الزكاة.',
    'onboarding_production_csid_failed' => 'فشل في الحصول على CSID الإنتاج',

    // General
    'current_environment' => 'البيئة الحالية',
    'back_to_settings' => 'العودة إلى الإعدادات',
    'loading' => 'جارٍ التحميل...',
    'please_wait' => 'يرجى الانتظار...',

    // Manual certificate import
    'import_certificate_title' => 'استيراد الشهادة يدوياً (أداة خارجية)',
    'import_certificate_desc' => 'الصق بيانات الشهادة المولدة من أداتك الخارجية (مثل تطبيق C#) لتخطي الخطوتين 1 و2.',
    'import_private_key_label' => 'المفتاح الخاص (PEM)',
    'import_csr_label' => 'CSR (Base64، اختياري)',
    'import_token_label' => 'رمز الأمان الثنائي (CSID الامتثال)',
    'import_secret_label' => 'الرمز السري',
    'import_request_id_label' => 'معرّف طلب الامتثال',
    'import_button' => 'استيراد وحفظ',
    'import_certificate_success' => 'تم استيراد بيانات الشهادة بنجاح! يمكنك الآن تشغيل فحوصات الامتثال (الخطوة 3).',
];
