from pyrogram import Client, filters
import requests
import time
import json
import os

API_ID = id gir
API_HASH = "hash gir"
BOT_TOKEN = "token gir"
DATA_FILE = "shortened_links.json"
COOLDOWN = 5

SHORTENER_API = "https://batin.tr/api.php"  # Kendi domainin buraya

app = Client("url_shortener_bot", api_id=API_ID, api_hash=API_HASH, bot_token=BOT_TOKEN)

user_cooldowns = {}

if os.path.exists(DATA_FILE):
    with open(DATA_FILE, "r", encoding="utf-8") as f:
        shortened_links = json.load(f)
else:
    shortened_links = {}

def save_links():
    with open(DATA_FILE, "w", encoding="utf-8") as f:
        json.dump(shortened_links, f, ensure_ascii=False, indent=2)

def shorten_url(long_url):
    try:
        response = requests.post(SHORTENER_API, json={"url": long_url}, timeout=10)
        data = response.json()
        if "short_url" in data:
            return data["short_url"]
    except Exception as e:
        print(f"Kısaltma hatası: {e}")
        return None
    return None

@app.on_message(filters.command("start") & (filters.private | filters.group))
async def start_cmd(client, message):
    await message.reply(
        "Merhaba! Ben URL Kısaltıcı botuyum.\n"
        "Bir linki kısaltmak için aşağıdaki komutu kullanabilirsin:\n"
        "/kısalt https://ornek.com\n\n"
        "Lütfen spam yapmaktan kaçın, komutlar arasında 5 saniye beklemelisin."
    )

@app.on_message(filters.command("kısalt") & (filters.private | filters.group))
async def shorten_handler(client, message):
    user_id = message.from_user.id
    now = time.time()

    last_time = user_cooldowns.get(user_id, 0)
    if now - last_time < COOLDOWN:
        await message.reply(f"⏳ Lütfen komutlar arasında en az {COOLDOWN} saniye bekleyin.")
        return
    user_cooldowns[user_id] = now

    if len(message.command) < 2:
        await message.reply("❗️ Lütfen kısaltmak istediğiniz URL'yi yazın.\nÖrnek: /kısalt https://ornek.com")
        return

    long_url = message.command[1]

    if not (long_url.startswith("http://") or long_url.startswith("https://")):
        await message.reply("❗️ Lütfen geçerli bir URL gönderin. 'http://' veya 'https://' ile başlamalı.")
        return

    if long_url in shortened_links:
        short_url = shortened_links[long_url]
        await message.reply(f"🔗 Bu link daha önce kısaltıldı:\n{short_url}")
        return

    short_url = shorten_url(long_url)

    if short_url:
        shortened_links[long_url] = short_url
        save_links()
        await message.reply(f"🔗 Kısaltılmış URL:\n{short_url}")
    else:
        await message.reply("⚠️ URL kısaltılırken bir hata oluştu.")

if __name__ == "__main__":
    app.run()
