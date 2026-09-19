# دليل التغييرات ونقاط الربط الخاصة بالفرونت إند (Frontend API Integration Guide)

توضح هذه الوثيقة الشاملة جميع التعديلات والتحسينات السلوكية التي تم تطبيقها على نقاط الربط (API Endpoints)، بالإضافة إلى الدليل الكامل للتعامل مع التمارين والميديا والصلاحيات.

---

## 1. التحديثات الرئيسية والإصلاحات المتفق عليها (Recent Fixes & Enhancements)

### 1.1 تاريخ الميلاد وحقل العمر للمريض (`GET /api/business/patients`)
- **المشكلة:** ظهور تاريخ ميلاد في المستقبل لبعض المرضى في بيانات الاختبار مما أدى لظهور `age: 0`.
- **التعديل:** تم تحديث بيانات الاختبار وتطبيق فحص جبري (`before:today`) لمنع إدخال تاريخ ميلاد مستقبل مستقبلاً.
- **النمط المسترجَع:** أصبح حقل `age` يرجع رقماً صحبحاً (مثل `29`) أو `null` في حال غياب تاريخ الميلاد.

### 1.2 حماية مسارات إدارة المعالجين (Therapists Security Authorization Gap)
- **المشكلة:** كان بإمكان المعالج (`role:therapist`) إضافة أو تعديل أو حذف معالجين آخرين بنفس المركز.
- **التعديل:** تم تخصيص جميع عمليات التحكم بالمعالجين (`POST`, `PUT`, `DELETE` على `/api/business/therapists`) لتكون حصرياً لمدير المركز (`role:admin`). أي محاولة من معالج ترجع استجابة `403 Forbidden`.

### 1.3 الاستعلام عن تذاكر الدعم الفني الخاصة بمراكز أخرى (`GET /api/business/support-tickets/{id}`)
- **المشكلة:** عند محاولة فتح تذكرة دعم لا تنتمي لمركز المستخدم الحالي، كانت الاستجابة ترجع خطأ `422 Validation Error`.
- **التعديل:** تم تعديل السلوك لترجع `404 Not Found` لتطابق معايير REST API عند محاولة الوصول لمورد غير موجود في نطاق صلاحيات المستخدم.

### 1.4 توحيد وسائط التمارين (`exercise_media`) وحجم الرفع Max Size
- **اسم مجموعة الميديا بالباك إند (Spatie Collection):** `exercise_media` (لتشمل الفيديو والصور).
- **اسم حقل الملف أثناء الرفع (Form-Data Field):** **`video`** هو الاسم المعتمَد القياسي (مع دعم `exercise_media`, `media`, `file` كبدائل مرنة).
- **مفتاح رابط الميديا في الاستجابة (JSON Response Key):** **`video_url`** (`string | null`).
- **الحد الأقصى لحجم الملف:** **`50MB`** (`51,200 KB`).
- **الامتدادات المدعومة:**
  - فيديوهات: `mp4`, `mov`, `avi`, `webm`, `mkv`, `m4v`
  - صور: `jpeg`, `png`, `jpg`, `webp`, `gif`

---

## 2. إدارة التمارين ونظام النطاقات والصلاحيات (Exercise Management & Scoping)

تم تقسيم التمارين إلى 3 مستويات نطاق (Scoping Tiers):
1. **تمارين عامة للنظام (`Global System Exercises`):**
   - `center_id = null`
   - `therapist_id = null`
   - `is_global = true`
2. **تمارين عامة للمركز (`Center Public Exercises`):**
   - `center_id = {center_id}`
   - `therapist_id = null`
   - `is_center_public = true`
3. **تمارين خاصة بمعالج (`Therapist Private Exercises`):**
   - `center_id = {center_id}`
   - `therapist_id = {therapist_id}`
   - `is_therapist_private = true`

### 2.1 سياسة الحذف والتعديل (Exercise Deletion & Mutation Rules)
- **التمارين العامة بالنظام (`is_global = true`):** لا يمكن حذفها أو تعديلها من أدمن السنتر أو المعالج (ترجع `403 Forbidden`). فقط اللاندلورد (`landlord`) يملك صلاحية حذفها أو تعديلها.
- **تمارين المركز العامة (`is_center_public = true`):** يمكن لأدمن السنتر أو اللاندلورد حذفها أو تعديلها.
- **تمارين المعالج الخاصة (`is_therapist_private = true`):** يمكن للمعالج الذي أنشأها، أو أدمن السنتر، أو اللاندلورد حذفها أو تعديلها.

---

## 3. النقاط المتاحة واستخداماتها (Endpoints Breakdown)

### أ. مسارات اللاندلورد (`/api/landlord/exercises`)

| الـ Method | الـ Endpoint | الوصف | المعاملات والـ Query Filters / Payload |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/landlord/exercises` | عرض قائمة التمارين مع الفلترة | **بدون فلتر:** ترجع التمارين العامة بالنظام (`is_global = true`).<br>**`?center_id=1`:** ترجع جميع تمارين المركز رقم 1 (العامة والخاصة بمعالجيه).<br>**`?center_id=1&therapist_id=5`:** ترجع تمارين معالج معين داخل المركز.<br>**`?search=كتف`:** بحث باسم التمرين. |
| `POST` | `/api/landlord/exercises` | إنشاء تمرين جديد | **Form-Data:**<br>`title` (مطلوب)<br>`center_id` (اختياري)<br>`therapist_id` (اختياري)<br>`default_sets`, `default_repeats`, `default_duration`, `therapist_notes`<br>`video` (ملف الميديا - حتى 50MB) |
| `GET` | `/api/landlord/exercises/{id}` | تفاصيل تمرين | ترجع بيانات التمرين الكاملة مع العلاقات (`center`, `therapist`) ورابط الميديا `video_url`. |
| `PUT` | `/api/landlord/exercises/{id}` | تعديل تمرين | تعديل البيانات + إرسال `video` فقط في حالة تغيير الميديا. |
| `DELETE` | `/api/landlord/exercises/{id}` | حذف تمرين | اللاندلورد يملك صلاحية حذف أي تمرين بالنظام. |

---

### ب. مسارات الأعمال والسناتر (`/api/business/exercises`)

| الـ Method | الـ Endpoint | الوصف | التفاصيل الصلاحية |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/business/exercises` | عرض التمارين المتاحة للمركز | ترجع التمارين العامة + تمارين المركز العامة + تمارين المعالج الحالي. |
| `POST` | `/api/business/exercises` | إنشاء تمرين جديد للمركز/المعالج | إذا أنشأه الأدمن يُحسب تمرين عام للمركز، وإذا أنشأه معالج يُحسب تمرين خاص به. الحجم الأقصى للميديا `video` هو 50MB. |
| `PUT` | `/api/business/exercises/{id}` | تعديل تمرين | يخضع لسياسة الملكية (403 إذا كان تمرين عام بالنظام). |
| `DELETE` | `/api/business/exercises/{id}` | حذف تمرين | يخضع لسياسة الملكية. |

---

### ج. مسارات المرضى (`/api/patient/exercises`)

| الـ Method | الـ Endpoint | الوصف | التفاصيل |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/patient/exercises` | عرض التمارين المسندة للمريض | ترجع قائمة التمارين المعينة للمريض حالياً مع تفاصيل الأهداف (`target_sets`, `target_repeats`, `target_duration`) ورابط الميديا `video_url` والتقدم اليومي `today_progress`. |

---

## 4. الهيكل القياسي لاستجابة التمرين (Standard JSON Payload Response)

```json
{
  "success": true,
  "message": "Exercise fetched successfully.",
  "data": {
    "id": 12,
    "center_id": 1,
    "therapist_id": 5,
    "title": "تمارين تقوية عضلات الظهر السفلى",
    "default_sets": 3,
    "default_repeats": 10,
    "default_duration": 60,
    "therapist_notes": "تكرار التمرين مرتين يومياً مع الحفاظ على استقامة الظهر",
    "is_global": false,
    "is_center_public": false,
    "is_therapist_private": true,
    "center": {
      "id": 1,
      "name": "مركز مسار الطبي"
    },
    "therapist": {
      "id": 5,
      "name": "د. أحمد علي"
    },
    "video_url": "http://api.massar.com/storage/media/12/back_exercise.mp4",
    "created_at": "2026-09-19T18:00:00.000000Z",
    "updated_at": "2026-09-19T18:00:00.000000Z"
  },
  "meta": null
}
```
