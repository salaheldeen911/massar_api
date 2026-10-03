# 🎨 دليل التعامل مع ثيم الألوان للفرونت إند (Center Theme Colors Guide)

يقدم هذا المستند كافة التفاصيل التي يحتاجها مطور الفرونت إند لتكامل ثيم الألوان الخاص بكل سنتر.

---

## 1. القيم الافتراضية للثيم (Default Theme Colors)
عند إنشاء أي سنتر جديد أو في حال عدم تعديل الألوان، يتم تعيين قيم الألوان الافتراضية تلقائياً:
- **Primary Color (`primary_color`)**: `#012053` (الكحلي)
- **Secondary Color (`secondary_color`)**: `#F05023` (البرتقالي)

---

## 2. الحصول على بيانات الألوان (Retrieving Center Theme Colors)

تأتي الألوان ضمن كائن السنتر (`center`) في جميع استجابات الـ API ذات الصلة (تسجيل الدخول، بيانات المستخدم الحالية، وتفاصيل السنتر).

### 📍 الإندبوينت المخصصة لتفاصيل السنتر:
`GET /api/business/center-details`

**Headers Required:**
```http
Authorization: Bearer {token}
Accept: application/json
```

**مثال للاستجابة الناجحة (Response Example):**
```json
{
  "success": true,
  "message": "Center details retrieved successfully.",
  "data": {
    "id": 1,
    "name": "Massar Center",
    "type": "institution",
    "email": "info@massar.com",
    "phone": "+201114543532",
    "facebook": "https://facebook.com/massar",
    "whatsapp": "+201114543532",
    "instagram": null,
    "linkedin": null,
    "primary_color": "#012053",
    "secondary_color": "#F05023",
    "logo_url": "https://example.com/storage/logo.png",
    "created_at": "2026-10-03T18:00:00Z",
    "updated_at": "2026-10-03T18:00:00Z"
  }
}
```

---

## 3. تعديل ثيم الألوان (Updating Center Theme Colors)

يمكن للأدمن تعديل اللون الأساسي أو الفرعي أو كليهما من خلال الإندبوينت التالية:

### 📍 إندبوينت التعديل:
`POST /api/business/center-details`  או  `PUT /api/business/center-details`

**Headers Required:**
```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body (مثال للتعديل):**
```json
{
  "primary_color": "#012053",
  "secondary_color": "#F05023"
}
```

> **ملاحظة للتحقق (Validation Rules):**
> - صيغة اللون يجب أن تكون Hex Code صحيح مثل: `#012053` أو `#FFF`.

---

## 4. كيفية التطبيق في الفرونت إند (CSS Variables Example)

يمكن استخدام الألوان القادمة من الـ API وتطبيقها كـ CSS Variables في جذر التطبيق:

```javascript
// مثال عند استلام بيانات السنتر
const { primary_color, secondary_color } = centerData;

document.documentElement.style.setProperty('--primary-color', primary_color);
document.documentElement.style.setProperty('--secondary-color', secondary_color);
```

وفي ملف الـ CSS:
```css
:root {
  --primary-color: #012053;
  --secondary-color: #F05023;
}

.btn-primary {
  background-color: var(--primary-color);
}

.accent-text {
  color: var(--secondary-color);
}
```
