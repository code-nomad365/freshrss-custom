# FreshRSS AI Assistant  

## 🚀 Introduction  
**FreshRSS AI Assistant** is a powerful extension that enhances your RSS reading experience by leveraging an **OpenAI-compatible LLM** (such as LiteLLM, OpenAI, or other compatible providers).  

Inspired by [LiangWei88/xExtension-ArticleSummary](https://github.com/LiangWei88/xExtension-ArticleSummary) and [reply2future/xExtension-NewsAssistant](https://github.com/reply2future/xExtension-NewsAssistant).

### 🔹 Features  
1️⃣ **Smart Retitling** – Automatically replaces clickbait headlines with clear, unbiased titles that reflect the actual content.  
2️⃣ **Executive Summaries** – Prepends a concise, factual summary at the start of each article, helping you decide what’s worth reading.  
3️⃣ **Auto-Tagging** – Assigns relevant tags to articles for better organization and discoverability.  
4️⃣ **Category Summarization** – Adds a **"Summarize" button** and API endpoint to generate a **digest of unread articles**, offering a quick way to stay informed.  
5️⃣ **Auto Mark-as-Read** *(Optional)* – Automatically marks summarized articles as read, helping you clear your queue effortlessly.  
Disclaimers: 
* This was ﮩ٨ـﮩﮩ٨ـ*vibe coded* ﮩ٨ـﮩﮩ٨ـfor personal use and is not intended for anything but an experiment and utility for my own homelab. It's been many years since I've touched PHP and my number one priority was expedience. That said, I'll do what I can to help if you notice any issues - please do raise them!
* Note that most commercial LLMs like OpenAI charge for use. Please be wary of cost.
* This extension uses temporary files for caching stored in `/tmp` and prefixed with `freshrss_`, you may want to clear them out periodically if you have limited temporary space.

---

## 📡 **API Example**  
Easily retrieve AI-generated summaries of unread articles in a category.  

```sh
# Extract cookies from your browser session via developer tools
# Set 'mark_read=1' to mark summarized articles as read, or omit/set to 0 to keep them unread.

curl -v -b 'FreshRSS=XXX; FreshRSS_login=YYY' \
     -X GET "https://freshrss.xxx/i/?c=AIAssistant&a=apiSummarize&cat_id=0&mark_read=1" \
     -H 'Accept: application/json'
```

🔹 **API Response:** JSON-formatted summary of unread articles.

---

## ⚙️ Configuration  

### **Article Processing Settings**  
- **OpenAI-compatible API URL**  
- **API Key**  
- **LLM Model (e.g., GPT-4o, Mistral, Llama 3, etc.)**  
- **Temperature (controls creativity)**  
- **Customizable Prompt for article processing**  

### **Summarization Settings**  
- **Summarization API URL**  
- **API Key**  
- **LLM Model**  
- **Temperature**  
- **Customizable Summarization Prompt**  
- **🔘 Toggle to auto-mark summarized articles as read**  

## **Global**
- **Token limit**

---

## 🛠️ Installation  
Install the extension by cloning the repository into the **FreshRSS extensions directory**:  

```sh
env FRESHRSS_DIR="/path/to/freshrss" git clone https://github.com/cvlc/freshrss-aiassist "$FRESHRSS_DIR/extensions/xExtension-AIAssistant"
```

Once installed, enable and configure the extension from the **FreshRSS Extensions settings** screen.  

---

## **📢 Stay Updated & Contribute**  
- Issues? Suggestions? Feel free to open an issue on GitHub!  
- Contributions are welcome — fork and submit a PR to improve the extension.  
