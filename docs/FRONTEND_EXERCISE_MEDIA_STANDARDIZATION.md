# دليل توحيد وسائط التمارين للفرونت إند (Exercise Media Frontend Guide)

توضح هذه الوثيقة الموصفة القياسية لإرفاق واستقبال الوسائط (فيديوهات وصور التمارين) عبر كافة نقاط الربط (API Endpoints) في النظام.

---

## 1. ملخص التغيير والمفهوم العام

- **اسم مجموعة الميديا في الباك إند (Collection Name):** تم اعتماد اسم المجموعة **`exercise_media`** بدلاً من `video` في مكتبة Spatie MediaLibrary، لكي تعكس إمكانية رفع أنواع مختلفة من وسائط التمارين (فيديوهات وصور توضيحية).
- **اسم الحقل عند الإرسال (Request Field Name):** الحقل القياسي المعتمَد في الـ `multipart/form-data` هو **`video`**.
- **المرونة (Aliases):** يدعم الباك إند أيضاً الأسماء (`exercise_media`, `media`, `file`) للتعامل مع أي حالات استثنائية.
- **اسم المعلمة في الاستجابة (API Response Key):** يرجع الرابط النهائي للميديا دائماً تحت المفتاح **`video_url`** (`string | null`).

---

## 2. مواصفات رفع الملفات (File Upload Specs)

عند إنشاء أو تعديل أي تمرين (`POST` / `PUT` / `PATCH`):

| الخاصية | القيمة |
| :--- | :--- |
| **Content-Type** | `multipart/form-data` |
| **اسم حقل الملف المفضّل** | `video` |
| **أنواع الملفات المدعومة** | **فيديوهات:** `mp4`, `mov`, `avi`, `webm`, `mkv`, `m4v`<br>**صور:** `jpeg`, `png`, `jpg`, `webp`, `gif` |
| **الحد الأقصى لحجم الملف** | `50MB` (51,200 KB) |

---

## 3. هيئة الطلب والاستجابة (Request & Response Payload)

### أ. مثال على إرسال الطلب (`POST /api/landlord/exercises` أو `POST /api/business/exercises`)

```http
POST /api/landlord/exercises HTTP/1.1
Host: api.massar.com
Authorization: Bearer {token}
Content-Type: multipart/form-data; boundary=----WebKitFormBoundary

------WebKitFormBoundary
Content-Disposition: form-data; name="title"

تمارين استطالة الكتف
------WebKitFormBoundary
Content-Disposition: form-data; name="default_sets"

3
------WebKitFormBoundary
Content-Disposition: form-data; name="default_repeats"

12
------WebKitFormBoundary
Content-Disposition: form-data; name="default_duration"

90
------WebKitFormBoundary
Content-Disposition: form-data; name="therapist_notes"

ملاحظات توضيحية للمريض
------WebKitFormBoundary
Content-Disposition: form-data; name="video"; filename="shoulder_exercise.mp4"
Content-Type: video/mp4

[BINARY DATA]
------WebKitFormBoundary--
```

---

### ب. مثال على استجابة النظام القياسية (JSON Response)

```json
{
  "success": true,
  "message": "Exercise fetched successfully.",
  "data": {
    "id": 15,
    "center_id": null,
    "therapist_id": null,
    "title": "تمارين استطالة الكتف",
    "default_sets": 3,
    "default_repeats": 12,
    "default_duration": 90,
    "therapist_notes": "ملاحظات توضيحية للمريض",
    "is_global": true,
    "is_center_public": false,
    "is_therapist_private": false,
    "center": null,
    "therapist": null,
    "video_url": "http://api.massar.com/storage/media/15/shoulder_exercise.mp4",
    "created_at": "2026-09-19T17:50:00+03:00",
    "updated_at": "2026-09-19T17:50:00+03:00"
  },
  "meta": null
}
```

> 📌 **ملاحظة:** في حال عدم إرفاق ملف ميديا، تكون قيمة `video_url` هي `null`.

---

## 4. مسارات API المتعلقة بالتمارين (Exercise API Endpoints)

### أولاً: مسارات اللاندلورد (`/api/landlord/exercises`)
- `GET /api/landlord/exercises`: عرض التمارين مع دعم الفلترة:
  - بدون فلتر: يرجع التمارين العامة بالنظام (`is_global = true`).
  - `?center_id=1`: يرجع جميع تمارين المركز المحدد (الخاصة بالمركز + الخاصة بمعالجي المركز).
  - `?center_id=1&therapist_id=5`: يرجع تمارين معالج معين داخل المركز.
- `POST /api/landlord/exercises`: إنشاء تمرين جديد (عام أو لمركز أو لمعالج).
- `GET /api/landlord/exercises/{id}`: تفاصيل تمرين محدد.
- `PUT /api/landlord/exercises/{id}` / `POST /api/landlord/exercises/{id}?_method=PUT`: تعديل تمرين.
- `DELETE /api/landlord/exercises/{id}`: حذف تمرين.

### ثانياً: مسارات الأعمال والسناتر (`/api/business/exercises`)
- `GET /api/business/exercises`: عرض التمارين المتاحة للمركز والمعالج.
- `POST /api/business/exercises`: إنشاء تمرين خاص بالمركز/المعالج.
- `PUT /api/business/exercises/{id}`: تعديل التمرين.
- `DELETE /api/business/exercises/{id}`: حذف تمرين (طبقاً لصلاحيات الأدمن والمعالج).

### ثالثاً: مسارات المرضى (`/api/patient/exercises`)
- `GET /api/patient/exercises`: عرض التمارين المعينة للمريض الحالي مع تفاصيل `video_url` و `today_progress`.

---

## 5. ملخص موجه للفرونت إند

1. قم بإرسال الملف تحت اسم الحقل `video` أثناء رفع الـ Form-Data.
2. اعتمد على حقل `video_url` في الـ JSON Response لعرض الميديا (سواء كانت فيديو أو صورة).
3. عند تحديث تمرين بدون تغيير الميديا، لا داعي لإرسال حقل `video`. أرسله فقط عند التغيير.
