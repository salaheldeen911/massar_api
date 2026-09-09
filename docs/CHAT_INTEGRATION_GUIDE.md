# دليل التكامل ونظام المحادثات اللحظية (Chat System Integration Guide) - Massar API

يقدم هذا المستند توثيقاً فنياً شاملاً لنظام المحادثات اللحظية (Real-time Chat System) الخاص بمشروع **Massar API** لتمكين فريق الواجهات الأمامية (Frontend Developers - React) من ربط وتنفيذ شاشات المحادثة، الاستماع إلى التنبيهات اللحظية عبر **Laravel Reverb (WebSockets)**، وإتاحة رفع المرفقات والوسائط.

---

## 1. المفاهيم الأساسية والأمان (Core Concepts & Security Rules)

1. **إسناد الرعاية (Caregiver Assignment)**:
   - المحادثة متاحة فقط بين **المريض** والمسؤول عنه المسند إليه في النظام (`patient_profiles.therapist_id`) سواء كان حسابه **معالج (`therapist`)** أو **أدمن (`admin`)**.
   - لا يمكن للمريض مراسلة معالج غير مسند إليه، ولا يمكن للمعالج/الأدمن مراسلة مريض غير مسند إليه.

2. **عزل المراكز الطبية (Strict Center Isolation - Multi-Tenancy)**:
   - الرسائل معزولة تماماً على مستوى المركز (`center_id`). يمنع إرسال أو استلام أي رسالة بين أطراف تنتمي لمراكز طبية مختلفة.

3. **الوسائط والمرفقات (Media Attachments)**:
   - تدعم الرسائل إرفاق الملفات (صور، تسجيلات صوتية، مستندات PDF، أو فيديوهات) ويتم إدارتها عبر مكتبة Spatie MediaLibrary وإعادة رابط المرفق في حقل `attachment_url`.

4. **الاتصال اللحظي (Real-Time WebSockets)**:
   - يعتمد النظام على خادم **Laravel Reverb** للبث الفوري للرسائل على قنوات مشفرة وخاصة بالمستخدم `private-user.{userId}`.

---

## 2. توثيق نقاط الربط (REST API Endpoints)

جميع الطلبات تتطلب هيدر التوثيق:
```http
Authorization: Bearer <Sanctum_Token>
Accept: application/json
```

---

### أ. مسارات المعالج والأدمن (`Business APIs`) - `/api/business/chat`

#### 1. جلب قائمة المحادثات (Assigned Patients Conversations)
- **Endpoint**: `GET /api/business/chat/conversations`
- **الوصف**: يُرجع قائمة المرضى المسندين للمعالج/الأدمن الحالي، مع نص ووقـت آخر رسالة، وعدد الرسائل غير المقروءة لكل مريض.
- **Response Success (200 OK)**:
```json
{
  "success": true,
  "message": "Conversations retrieved successfully.",
  "data": [
    {
      "patient": {
        "id": 5,
        "name": "سيف محمد",
        "email": "patient@massar.com",
        "phone": "+201087654321",
        "avatar_url": "https://massar.test/storage/media/avatar.jpg"
      },
      "unread_count": 2,
      "last_message": "دكتور، لقد أكملت تمارين الركبة لليوم.",
      "last_message_has_attachment": false,
      "last_message_at": "2026-09-09T22:30:00.000000Z"
    }
  ]
}
```

---

#### 2. جلب أرشيف الرسائل مع مريض معين (Get Patient Messages History)
- **Endpoint**: `GET /api/business/chat/messages/{patientId}?per_page=20`
- **الوصف**: جلب أرشيف المحادثة مع المريض بشكل مرقم (Paginated). يتم تعليم الرسائل غير المقروءة الواردة كمقروءة تلقائياً عند طلب هذا المسار.
- **Response Success (200 OK)**:
```json
{
  "success": true,
  "message": "Messages retrieved successfully.",
  "data": [
    {
      "id": 15,
      "center_id": 1,
      "sender_id": 5,
      "receiver_id": 2,
      "message": "دكتور، هل يمكنني زيادة تكرار التمرين؟",
      "read_at": "2026-09-09T22:35:00.000000Z",
      "created_at": "2026-09-09T22:30:00.000000Z",
      "attachment_url": null,
      "attachment_type": null,
      "sender": {
        "name": "سيف محمد",
        "phone": "+201087654321",
        "roles": ["patient"]
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

---

#### 3. إرسال رسالة إلى مريض (Send Message to Patient)
- **Endpoint**: `POST /api/business/chat/messages`
- **Header**: `Content-Type: multipart/form-data` (في حال إرسال ملف) أو `application/json`.
- **Request Body**:
  - `receiver_id` (required|integer): ID المريض المستهدف.
  - `message` (required_without:attachment|string): نص الرسالة (حتى 5000 حرف).
  - `attachment` (required_without:message|file): ملف مرفق (jpg, png, pdf, mp4, m4a, mp3, wav) بحد أقصى 10MB.
- **Response Success (201 Created)**:
```json
{
  "success": true,
  "message": "Message sent successfully.",
  "data": {
    "id": 16,
    "center_id": 1,
    "sender_id": 2,
    "receiver_id": 5,
    "message": "ممتاز جداً! استمر بنفس المعدل.",
    "read_at": null,
    "created_at": "2026-09-09T22:36:00.000000Z",
    "attachment_url": null,
    "attachment_type": null
  }
}
```

---

#### 4. تحديد رسائل المريض كمقروءة (Mark Patient Messages as Read)
- **Endpoint**: `POST /api/business/chat/messages/{patientId}/read`
- **Response Success (200 OK)**:
```json
{
  "success": true,
  "message": "Messages marked as read successfully.",
  "data": {
    "updated_count": 3
  }
}
```

---

### ب. مسارات المريض (`Patient APIs`) - `/api/patient/chat`

#### 1. جلب أرشيف الرسائل مع المعالج المسند (Get Caregiver Messages)
- **Endpoint**: `GET /api/patient/chat/messages?per_page=20`
- **الوصف**: يرجع الرسائل بين المريض والمعالج/الأدمن المسند إليه تلقائياً.

#### 2. إرسال رسالة إلى المعالج (Send Message to Caregiver)
- **Endpoint**: `POST /api/patient/chat/messages`
- **Request Body**:
  - `message` (optional_if_attachment|string): نص الرسالة.
  - `attachment` (optional_if_message|file): المرفق (صورة أو تسجيل صوتي أو مستند).
  - `receiver_id` (optional|integer): اختياري، حيث يتم توجيه الرسالة تلقائياً للمعالج المسند.

#### 3. تحديد رسائل المعالج كمقروءة (Mark Caregiver Messages as Read)
- **Endpoint**: `POST /api/patient/chat/read`

---

## 3. خطوات التنفيذ في مشروع React (React Integration Steps)

### الخطوة 1: تثبيت المكتبات المطلوبة (Install Dependencies)

قم بتثبيت `laravel-echo` و `pusher-js` في تطبيق React:
```bash
npm install laravel-echo pusher-js
```

---

### الخطوة 2: تهيئة خادوم WebSockets (Echo Setup)

قم بإنشاء ملف `src/services/echo.js`:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export const createEchoInstance = (token) => {
  return new Echo({
    broadcaster: 'reverb',
    key: process.env.REACT_APP_REVERB_APP_KEY || 'massar_reverb_key',
    wsHost: process.env.REACT_APP_REVERB_HOST || window.location.hostname,
    wsPort: process.env.REACT_APP_REVERB_PORT || 8080,
    wssPort: process.env.REACT_APP_REVERB_PORT || 443,
    forceTLS: (process.env.REACT_APP_REVERB_SCHEME || 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${process.env.REACT_APP_API_BASE_URL}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: 'application/json',
      },
    },
  });
};
```

---

### الخطوة 3: الاستماع للرسائل اللحظية (Listening to Real-time Events)

في مكون الـ Chat الخاص بـ React (`ChatWindow.jsx`):

```javascript
import React, { useEffect, useState, useRef } from 'react';
import { createEchoInstance } from '../services/echo';
import axios from 'axios';

export const ChatWindow = ({ currentUser, activePeerId, userRole }) => {
  const [messages, setMessages] = useState([]);
  const [inputText, setInputText] = useState('');
  const [selectedFile, setSelectedFile] = useState(null);
  const echoRef = useRef(null);
  const messagesEndRef = useRef(null);

  // 1. جلب أرشيف الرسائل عند فتح الشات
  useEffect(() => {
    fetchMessages();
  }, [activePeerId]);

  const fetchMessages = async () => {
    const endpoint = userRole === 'patient' 
      ? '/api/patient/chat/messages' 
      : `/api/business/chat/messages/${activePeerId}`;
      
    const response = await axios.get(endpoint);
    setMessages(response.data.data.reverse()); // ترتيب تصاعدي حسب الوقت
  };

  // 2. تفعيل الاشتراك اللحظي عبر Laravel Echo
  useEffect(() => {
    const token = localStorage.getItem('token');
    const echo = createEchoInstance(token);
    echoRef.current = echo;

    // الاستماع للقناة الخاصة بالمستخدم الحالي
    echo.private(`user.${currentUser.id}`)
      .listen('.message.sent', (e) => {
        console.log('رسالة جديدة قادمة:', e.message);
        
        // التحقق مما إذا كانت الرسالة من الشخص الذي نحادثه حالياً
        if (e.message.sender_id === activePeerId || userRole === 'patient') {
          setMessages((prev) => [...prev, e.message]);
          scrollToBottom();
        }
      });

    return () => {
      if (echoRef.current) {
        echoRef.current.leave(`user.${currentUser.id}`);
      }
    };
  }, [currentUser.id, activePeerId]);

  // 3. إرسال رسالة جديدة
  const handleSendMessage = async (e) => {
    e.preventDefault();
    if (!inputText.trim() && !selectedFile) return;

    const formData = new FormData();
    if (userRole !== 'patient') {
      formData.append('receiver_id', activePeerId);
    }
    if (inputText) formData.append('message', inputText);
    if (selectedFile) formData.append('attachment', selectedFile);

    const endpoint = userRole === 'patient'
      ? '/api/patient/chat/messages'
      : '/api/business/chat/messages';

    try {
      const response = await axios.post(endpoint, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });

      // إضافة الرسالة الواصلة للاستجابة فوراً (Optimistic UI)
      setMessages((prev) => [...prev, response.data.data]);
      setInputText('');
      setSelectedFile(null);
      scrollToBottom();
    } catch (error) {
      console.error('خطأ في إرسال الرسالة:', error.response?.data?.message);
    }
  };

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  return (
    <div className="chat-container">
      <div className="messages-list">
        {messages.map((msg) => (
          <div key={msg.id} className={`message-bubble ${msg.sender_id === currentUser.id ? 'sent' : 'received'}`}>
            {msg.message && <p>{msg.message}</p>}
            {msg.attachment_url && (
              <div className="attachment-preview">
                {msg.attachment_type?.startsWith('image/') ? (
                  <img src={msg.attachment_url} alt="مرفق" />
                ) : (
                  <a href={msg.attachment_url} target="_blank" rel="noreferrer">تحميل المرفق 📎</a>
                )}
              </div>
            )}
            <span className="timestamp">{new Date(msg.created_at).toLocaleTimeString()}</span>
          </div>
        ))}
        <div ref={messagesEndRef} />
      </div>

      <form onSubmit={handleSendMessage} className="chat-input-form">
        <input 
          type="text" 
          value={inputText} 
          onChange={(e) => setInputText(e.target.value)} 
          placeholder="اكتب رسالتك هنا..." 
        />
        <input 
          type="file" 
          onChange={(e) => setSelectedFile(e.target.files[0])} 
        />
        <button type="submit">إرسال 🚀</button>
      </form>
    </div>
  );
};
```

---

## 4. إعدادات البيئة والتجهيز على البرودكشن والدكر (Production & Docker Setup)

المشروع يدار بالكامل عبر **Docker & Docker Compose**. لضمان عمل خادم WebSockets (Reverb) بكفاءة على السيرفر في بيئة الإنتاج:

### أ. ملفات البيئة (`.env.example` / `.env.production`)

#### 1. متغيرة الـ Backend (.env في Laravel):
```env
# Reverb Server Configuration
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=massar_app
REVERB_APP_KEY=massar_reverb_key
REVERB_APP_SECRET=massar_reverb_secret
REVERB_HOST="0.0.0.0"
REVERB_PORT=8080
REVERB_SCHEME=https

# Public Client Configuration
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="api.massar.com"
VITE_REVERB_PORT="443"
VITE_REVERB_SCHEME="https"
```

#### 2. متغيرة الـ React Frontend (.env):
```env
REACT_APP_API_BASE_URL=https://api.massar.com
REACT_APP_REVERB_APP_KEY=massar_reverb_key
REACT_APP_REVERB_HOST=api.massar.com
REACT_APP_REVERB_PORT=443
REACT_APP_REVERB_SCHEME=https
```

---

### ب. تكوين الدكر (`docker-compose.yml`)

تأكد من وجود خدمة `reverb` تعمل بشكل مستقل في ملف `docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: massar_app
    restart: always
    environment:
      - BROADCAST_CONNECTION=reverb
    volumes:
      - .:/var/www/html
    networks:
      - massar_network

  reverb:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: massar_reverb
    restart: always
    command: php artisan reverb:start --host=0.0.0.0 --port=8080
    ports:
      - "8080:8080"
    environment:
      - BROADCAST_CONNECTION=reverb
    volumes:
      - .:/var/www/html
    networks:
      - massar_network

networks:
  massar_network:
    driver: bridge
```

---

### ج. إعدادات Nginx Reverse Proxy لـ WebSockets (Nginx SSL Configuration)

على سيرفر الإنتاج (Nginx)، يجب توجيه اتصالات WebSockets لخدمة Reverb على البورت `8080`:

```nginx
server {
    listen 443 ssl http2;
    server_name api.massar.com;

    ssl_certificate /etc/letsencrypt/live/api.massar.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/api.massar.com/privkey.pem;

    # Nginx Proxy for API Requests
    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # Nginx Reverse Proxy for Laravel Reverb WebSockets
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_read_timeout 60s;
        proxy_send_timeout 60s;
    }
}
```

---

## 5. ملخص الفحص وتفاصيل التفعيل (Verification Checklist)

- [x] **عزل السناتر**: تم فحص وتأكيد منع المراسلة بين المراكز المختلفة (تُرجع استجابة `403 Forbidden`).
- [x] **إسناد المعالج**: تم فحص اقتصار الدردشة على المعالج/الأدمن المسند فقط.
- [x] **إرفاق الملفات**: يدعم رفع الملفات عبر Spatie MediaLibrary وإرجاع رابط `attachment_url`.
- [x] **الاختبارات الآلية**: نجاح جميع الاختبارات الآلية (`vendor/bin/phpunit` - 88 passed).
- [x] **مجموعة Postman**: تم تحديث ومشاركة جميع نقاط الربط في ملف `postman/Massar_API.postman_collection.json`.
