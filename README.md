# 🔗 URL Kısaltıcı Telegram Botu

Bu proje, kendi barındırdığınız bir **PHP tabanlı URL kısaltma servisi** ile entegre çalışan bir **Telegram botudur**. Kullanıcılar Telegram üzerinden bir link gönderdiğinde, bot bu bağlantıyı sizin servisiniz aracılığıyla kısaltır ve hızlıca geri gönderir.

## 🚀 Özellikler

- ✅ Telegram üzerinden link gönderimiyle otomatik kısaltma
- 🔐 Sadece bot sahibi veya grup izinleriyle sınırlı kullanım (isteğe bağlı)
- ⚙️ PHP backend API entegrasyonu
- 💬 Hatalı link / boş mesaj kontrolü
- ☁️ cPanel uyumlu sunucu yapısı
- 🌓 Light ve Dark mode dostu tasarım (Telegram temasıyla uyumlu)


---

## 🧱 Gereksinimler

### Python Gereksinimleri

- Python 3.8+
- [pyrogram](https://docs.pyrogram.org/)
- tgcrypto (opsiyonel ama tavsiye edilir)

```bash
pip install pyrogram tgcrypto requests
