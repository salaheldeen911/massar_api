# دليل التكامل والربط لبوابات النظام - Massar API (Frontend Integration Guide)

يقدم هذا المستند توثيقاً شاملاً للبنية التحتية، الأدوار (Roles)، الهيكلية المعيارية للاستجابات (JSON Schema)، والروابط الخاصة بنظام **Massar API** لإرشاد فريق التطوير الواجهاتي (Frontend).

---

## 1. المفاهيم الأساسية وتعدد المستأجرين (Multi-Tenancy & Center Scoping)

- **المركز الطبي (`Center`)**:
  - يتكون النظام من مراكز طبية متعددة. كل بيانات المرضى، الأخصائيين، والخطط العلاجية ترتبط بـ `center_id`.
  - يتم التعرّف على مركز المستخدم تلقائياً من خلال التوكن الخاص به (`Bearer Token`) عند تسجيل الدخول، ولا يحتاج الفرونت إند لإرسال `center_id` في كل طلب يدوياً.
- **مسؤول النظام العام (`Landlord`)**:
  - حساب عام لإدارة النظام ككل (Super Admin). ليس مرتبطاً بمركز معين (`center_id = null`) ولديه صلاحيات شاملة على جميع المراكز.

---

## 2. الأدوار والصلاحيات (Roles & Responsibilities)

يتضمن النظام **4 أدوار رئيسية**، ولكل دور نطاق عمل ومسارات API مخصصة:

| اسم الدور في الـ Database (`role`) | اسم الشاشة / المستخدم (Role Label) | الوصف والنطاق (Scope & Responsibility) | بادئة المسارات (API Prefix) |
| --- | --- | --- | --- |
| **`landlord`** | أدمن النظام العام (Super Admin) | إدارة المراكز الطبية، الاشتراكات، تفعيل/تعطيل الحسابات، الدعم الفني العام. | `/api/landlord` |
| **`admin`** | أدمن المركز الطبي (Center Admin) | إدارة أخصائيي المركز، متابعة المرضى، تعديل بيانات المركز، الدعم الفني للمركز. | `/api/business` أو `/api/admin` |
| **`therapist`** | الأخصائي / المعالج (Therapist) | إعداد الخطط العلاجية، خطط التغذية، تعيين التمارين للمرضى، متابعة التقييمات. | `/api/business` أو `/api/therapist` |
| **`patient`** | المريض (Patient) | عرض الخطة العلاجية والتغذوية الخاصة به، تسجيل التمارين المنفذة ومتابعة تقدمه. | `/api/patient` |

---

## 3. بادئات المسارات وهيكلية الـ APIs (Route Structure)

### أ. المسارات العامة للجميع (Public & Auth Routes)
- **البادئة**: `/api`
- **أهم المسارات**:
  - `POST /api/login`: تسجيل الدخول وإرجاع `token` ودور المستخدم `role`.
  - `POST /api/register`: تسجيل حساب جديد.
  - `POST /api/logout`: تسجيل الخروج (يتطلب Auth Token).
  - `GET /api/me`: جلب بيانات المستخدم الحالي مع المركز والصلاحيات.
  - `POST /api/profile`: تحديث الملف الشخصي والصورة.
  - `PUT /api/profile/password`: تغيير كلمة المرور.
  - `GET /api/diagnoses`: جلب قائمة التشخيصات المتاحة في النظام.

### ب. مسارات أدمن النظام العام (`landlord`)
- **البادئة**: `/api/landlord` (يتطلب هيدر Auth وتوفر دور `landlord`)
- **أهم المسارات**:
  - `GET /api/landlord/dashboard`: إحصائيات النظام العامة.
  - `GET /api/landlord/centers`: إدارة المراكز الطبية (عرض، إضافة، تعديل، حذف).
  - `GET /api/landlord/centers/pending`: عرض طلبات انضمام المراكز المعلقة.
  - `POST /api/landlord/centers/{id}/approve`: القبول والتفعيل.
  - `POST /api/landlord/centers/{id}/reject`: الرفض.
  - `GET /api/landlord/support-tickets`: إدارة تذاكر الدعم الفني والرد عليها.

### ج. مسارات الأعمال للمركز (`admin` & `therapist`)
- **البادئة**: `/api/business` (يتطلب هيدر Auth وتوفر دور `admin` أو `therapist`)
- **أهم المسارات**:
  - `GET /api/business/dashboard`: لوحة إحصائيات المركز.
  - `GET /api/business/center-details`: تفاصيل المركز (خاص بـ `admin` فقط).
  - `POST /api/business/center-details`: تحديث بيانات وشعار المركز (`admin` فقط).
  - `GET|POST /api/business/patients`: إدارة مرادفى وحسابات المرضى بالمركز.
  - `GET|POST /api/business/therapists`: إدارة الأخصائيين بالمركز.
  - `POST /api/business/patients/{patient}/treatment-plan`: إنشاء/تحديث الخطة العلاجية للمريض.
  - `POST /api/business/patients/{patient}/nutrition-plan`: إنشاء/تحديث الخطة التغذوية للمريض.
  - `GET|POST /api/business/exercises`: إدارة بنك التمارين بالمركز.
  - `POST /api/business/patients/{patient}/exercises`: تعيين تمارين للمريض.
  - `DELETE /api/business/patients/{patient}/exercises/{exerciseId}`: إلغاء تعيين تمرين.
  - `GET|POST /api/business/support-tickets`: تقديم تذاكر دعم فني لـ Landlord.

### د. مسارات تطبيق المريض (`patient`)
- **البادئة**: `/api/patient` (يتطلب هيدر Auth وتوفر دور `patient`)
- **أهم المسارات**:
  - `GET /api/patient/treatment-plan`: عرض الخطة العلاجية الخاصة بالمريض.
  - `GET /api/patient/nutrition-plan`: عرض الخطة التغذوية الخاصة بالمريض.
  - `GET /api/patient/exercises`: عرض قائمة التمارين المطلوبة منه.
  - `POST /api/patient/exercises/{patientExercise}/log`: تسجيل إجراء تمرين (إرسال العدادات والوقت).
  - `POST /api/patient/exercises/{patientExercise}/complete`: تعليم التمرين كمكتمل.

---

## 4. المخطط الموحد للاستجابات (Unified Response JSON Schema)

جميع الـ APIs ترجع **نفس الهيكل الموحد** لتسهيل المعالجة في الفرونت إند:

### أ. استجابة النجاح (Success Response)
```json
{
  "success": true,
  "message": "تم العملية بنجاح",
  "data": {
    "id": 1,
    "name": "أحمد محمود",
    "email": "patient@example.com"
  },
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```
*ملاحظة*: كائن `meta` متاح فقط في حالات القوائم المصفحة (Paginated Data).

### ب. استجابة الخطأ (Error Response)
```json
{
  "success": false,
  "message": "بيانات غير صالحة",
  "errors": {
    "email": [
      "البريد الإلكتروني مستخدم من قبل"
    ],
    "phone": [
      "رقم الهاتف غير صحيح"
    ]
  }
}
```

---

## 5. الهيدرز المطلوبة (Required Request Headers)

في كل طلب موجه للـ API، يجب إرسال الهيدرز التالية:

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {YOUR_SANCTUM_TOKEN}
```

---

## 6. التعامل مع المرفقات والصور (Media Handling)

- يتم إدارة جميع الصور والمستندات عبر مكتبة **Spatie MediaLibrary**.
- الصور لا ترجع كـ String عادي، بل تأتي على هيئة كائنات مخصصة أو روابط كاملة (`full_url`) قابلة للعرض مباشرة في عناصر `<img>`.
- **عند رفع الملفات (Upload)**: يجب إرسال الطلب بصيغة `multipart/form-data`.

---

## 7. جدول المصطلحات والمسميات الموحدة (Naming Dictionary)

لتجنب أي التباس في المسميات بين الباك إند والفرونت إند:

| المصطلح الباك إند (Key) | المعنى بالعربية | النوع / الملاحظات |
| --- | --- | --- |
| `Center` | المركز الطبي | كيان المركز المستأجر |
| `Therapist` | الأخصائي / المعالج | حساب مستخدم بدور `therapist` |
| `Patient` | المريض | حساب مستخدم بدور `patient` |
| `Diagnosis` | التشخيص الطبي | تصنيف تشخيص المريض |
| `TreatmentPlan` | الخطة العلاجية | الخطة المصممة للمريض |
| `NutritionPlan` | الخطة التغذوية | النظام الغذائي المخصص |
| `Exercise` | التمرين | تمرين عادي في البنك |
| `PatientExercise` | تمرين المريض المخصص | تمرين تم تعيينه لمريض بجدول ومعايير محددة |
| `SupportTicket` | تذكرة دعم فني | تذكرة بلاغ أو استفسار |
