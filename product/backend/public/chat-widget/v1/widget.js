var Qu=Object.defineProperty;var Yu=(ae,le,y)=>le in ae?Qu(ae,le,{enumerable:!0,configurable:!0,writable:!0,value:y}):ae[le]=y;var m=(ae,le,y)=>Yu(ae,typeof le!="symbol"?le+"":le,y);(function(ae){"use strict";let le=class{constructor(){m(this,"debugMode",!1)}setDebug(e){this.debugMode=e}isDebug(){return this.debugMode}formatPrefix(e){return`[ApriloWidget:${e}]`}debug(e,t,...i){this.debugMode&&console.log(`%c${this.formatPrefix(e)}`,"color: #22C55E; font-weight: bold;",t,...i)}info(e,t,...i){console.info(`%c${this.formatPrefix(e)}`,"color: #0284C7; font-weight: bold;",t,...i)}warn(e,t,...i){console.warn(`%c${this.formatPrefix(e)}`,"color: #F59E0B; font-weight: bold;",t,...i)}error(e,t,...i){console.error(`%c${this.formatPrefix(e)}`,"color: #EF4444; font-weight: bold;",t,...i)}};const y=new le;class qo{constructor(){m(this,"_isOpen",!1);m(this,"_isLoading",!1);m(this,"_isTyping",!1);m(this,"_unreadCount",0);m(this,"_config",null);m(this,"_conversation",null);m(this,"_messages",[]);m(this,"_mode","ai");m(this,"_error",null);m(this,"listeners",new Set)}subscribe(e){return this.listeners.add(e),()=>this.listeners.delete(e)}notify(e){y.debug("State",`Event: ${e}`),this.listeners.forEach(t=>{try{t(e)}catch(i){y.error("State",`Listener error for ${e}`,i)}})}get isOpen(){return this._isOpen}get isLoading(){return this._isLoading}get isTyping(){return this._isTyping}get unreadCount(){return this._unreadCount}get config(){return this._config}get conversation(){return this._conversation}get messages(){return[...this._messages]}get mode(){return this._mode}get error(){return this._error}setOpen(e){this._isOpen!==e&&(this._isOpen=e,e&&(this._unreadCount=0,this.notify("unread_change")),this.notify("open_change"))}toggleOpen(){this.setOpen(!this._isOpen)}setLoading(e){this._isLoading=e}setConfig(e){this._config=e,this.notify("config_change")}setConversation(e){this._conversation=e,e!=null&&e.mode&&(this._mode=e.mode,this.notify("mode_change")),this.notify("conversation_change")}setMessages(e){this._messages=[...e],this.notify("messages_change")}clearMessages(){this._messages=[],this.notify("messages_change")}addMessage(e){this._messages.some(t=>t.id===e.id)||(this._messages.push(e),!this._isOpen&&e.sender_type!=="user"&&(this._unreadCount++,this.notify("unread_change")),this.notify("messages_change"))}setTyping(e){this._isTyping!==e&&(this._isTyping=e,this.notify("typing_change"))}setMode(e){this._mode!==e&&(this._mode=e,this.notify("mode_change"))}setError(e){this._error=e,this.notify("error_change")}reset(){this._isOpen=!1,this._isLoading=!1,this._isTyping=!1,this._unreadCount=0,this._conversation=null,this._messages=[],this._mode="ai",this._error=null,this.notify("open_change"),this.notify("messages_change")}}class Ko{constructor(e){m(this,"element");m(this,"badge");m(this,"iconContainer");this.state=e,this.element=document.createElement("button"),this.element.className="aprilo-launcher",this.element.setAttribute("type","button"),this.element.setAttribute("aria-label","Open support chat"),this.iconContainer=document.createElement("div"),this.iconContainer.className="aprilo-launcher-icon",this.renderIcon(!1),this.badge=document.createElement("span"),this.badge.className="aprilo-launcher-badge",this.badge.style.display="none",this.element.appendChild(this.iconContainer),this.element.appendChild(this.badge),this.element.addEventListener("click",()=>{this.state.toggleOpen()}),this.state.subscribe(t=>{if(t==="open_change"){const i=this.state.isOpen;this.renderIcon(i),this.element.setAttribute("aria-label",i?"Close support chat":"Open support chat")}else t==="unread_change"?this.updateBadge():t==="config_change"&&(this.applyConfig(),this.renderIcon(this.state.isOpen))})}getElement(){return this.element}applyConfig(){const e=this.state.config;e&&(e.widget.position==="bottom-left"?this.element.classList.add("position-bottom-left"):this.element.classList.remove("position-bottom-left"),e.behavior.show_launcher?this.element.style.display="flex":this.element.style.display="none")}updateBadge(){const e=this.state.unreadCount;e>0&&!this.state.isOpen?(this.badge.textContent=e>9?"9+":String(e),this.badge.style.display="flex"):this.badge.style.display="none"}renderIcon(e){var t,i;if(e)this.iconContainer.innerHTML=`
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      `;else{const s=this.state.config,r=(t=s==null?void 0:s.widget)==null?void 0:t.icon,o=(i=s==null?void 0:s.widget)==null?void 0:i.avatar_url;o?this.iconContainer.innerHTML=`<img src="${o}" alt="chat" style="width:28px;height:28px;border-radius:50%;object-fit:cover;" />`:r==="star"?this.iconContainer.innerHTML=`
          <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
          </svg>
        `:r==="support"?this.iconContainer.innerHTML=`
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
          </svg>
        `:r==="robot"?this.iconContainer.innerHTML=`
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2 2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"></path>
            <rect x="4" y="8" width="16" height="12" rx="4"></rect>
            <circle cx="9" cy="13" r="1.5" fill="currentColor"></circle>
            <circle cx="15" cy="13" r="1.5" fill="currentColor"></circle>
            <path d="M9 17h6"></path>
          </svg>
        `:this.iconContainer.innerHTML=`
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
        `}}}function Zi(n){if(typeof n!="string")return!1;const e=n.trim();return e?/^pk_(live|test)_[a-zA-Z0-9_\-]+$/.test(e):!1}function ie(n){return n.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}function Qo(n){try{return new Date(n).toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"})}catch{return""}}class Yo{constructor(e){m(this,"element");m(this,"nameEl");m(this,"statusEl");m(this,"avatarEl");this.state=e,this.element=document.createElement("header"),this.element.className="aprilo-header";const t=document.createElement("div");t.className="aprilo-header-info",this.avatarEl=document.createElement("div"),this.avatarEl.className="aprilo-header-avatar";const i=document.createElement("div");i.className="aprilo-header-titles",this.nameEl=document.createElement("div"),this.nameEl.className="aprilo-header-name",this.nameEl.textContent="Aprilo Support",this.statusEl=document.createElement("div"),this.statusEl.className="aprilo-header-status",this.statusEl.innerHTML=`
      <span class="aprilo-status-dot"></span>
      <span>Online</span>
    `,i.appendChild(this.nameEl),i.appendChild(this.statusEl),t.appendChild(this.avatarEl),t.appendChild(i);const s=document.createElement("div");s.className="aprilo-header-actions";const r=document.createElement("button");r.className="aprilo-header-btn",r.setAttribute("type","button"),r.setAttribute("aria-label","Close chat"),r.innerHTML=`
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    `,r.addEventListener("click",()=>{this.state.setOpen(!1)}),s.appendChild(r),this.element.appendChild(t),this.element.appendChild(s),this.state.subscribe(o=>{(o==="config_change"||o==="mode_change")&&this.render()}),this.render()}getElement(){return this.element}render(){var r,o,a,l,c;const e=this.state.config,t=this.state.mode,i=((r=e==null?void 0:e.ai)==null?void 0:r.display_name)||((o=e==null?void 0:e.widget)==null?void 0:o.name)||"Aprilo Assistant";this.nameEl.textContent=t==="human"?"Support Specialist":i;const s=(a=e==null?void 0:e.widget)==null?void 0:a.icon;(l=e==null?void 0:e.widget)!=null&&l.avatar_url?this.avatarEl.innerHTML=`<img src="${ie(e.widget.avatar_url)}" alt="${ie(i)}" />`:s==="star"?this.avatarEl.innerHTML=`
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
      `:s==="support"?this.avatarEl.innerHTML=`
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 18v-6a9 9 0 0 1 18 0v6"></path>
          <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path>
        </svg>
      `:s==="chat"?this.avatarEl.innerHTML=`
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
      `:this.avatarEl.innerHTML=`
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2 2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"></path>
          <rect x="4" y="8" width="16" height="12" rx="4"></rect>
          <circle cx="9" cy="13" r="1.5" fill="currentColor"></circle>
          <circle cx="15" cy="13" r="1.5" fill="currentColor"></circle>
          <path d="M9 17h6"></path>
        </svg>
      `,t==="human"?this.statusEl.innerHTML=`
        <span class="aprilo-status-dot" style="background: #60A5FA; box-shadow: 0 0 8px #3B82F6;"></span>
        <span>Human Support Connected</span>
      `:((c=e==null?void 0:e.support)==null?void 0:c.is_online)===!1?this.statusEl.innerHTML=`
        <span class="aprilo-status-dot" style="background: #9CA3AF; box-shadow: none;"></span>
        <span>Offline</span>
      `:this.statusEl.innerHTML=`
        <span class="aprilo-status-dot"></span>
        <span>Online</span>
      `}}class Jo{constructor(e,t){m(this,"element");this.state=e,this.onQuickAction=t,this.element=document.createElement("div"),this.element.className="aprilo-welcome-card",this.state.subscribe(i=>{i==="config_change"&&this.render()}),this.render()}getElement(){return this.element}setVisible(e){this.element.style.display=e?"block":"none"}render(){var o;const e=this.state.config;if(!e)return;const t=((o=e.organization)==null?void 0:o.name)||"Aprilo Support",i=e.widget.welcome_message||"Hi! How can we help you today?",s=e.quick_actions||[];let r="";s.length>0&&(r=`
        <div class="aprilo-quick-actions">
          ${s.map(a=>`
            <button type="button" class="aprilo-quick-btn" data-action-id="${ie(a.id)}">
              <span>${ie(a.label)}</span>
              <span class="aprilo-quick-btn-arrow">&rarr;</span>
            </button>
          `).join("")}
        </div>
      `),this.element.innerHTML=`
      <div class="aprilo-welcome-heading">Welcome to ${ie(t)}</div>
      <div class="aprilo-welcome-text">${ie(i)}</div>
      ${r}
    `,this.element.querySelectorAll(".aprilo-quick-btn").forEach(a=>{a.addEventListener("click",()=>{const l=a.getAttribute("data-action-id"),c=s.find(d=>d.id===l);c&&this.onQuickAction(c)})})}}class Xo{constructor(e){m(this,"element");this.state=e,this.element=document.createElement("div"),this.element.className="aprilo-messages-list",this.element.style.display="flex",this.element.style.flexDirection="column",this.element.style.gap="10px",this.state.subscribe(t=>{(t==="messages_change"||t==="mode_change")&&this.render()}),this.render()}getElement(){return this.element}scrollToBottom(e=!0){const t=this.element.parentElement;t&&t.scrollTo({top:t.scrollHeight,behavior:e?"smooth":"auto"})}render(){const e=this.state.messages,t=this.state.mode;let i="";e.forEach(s=>{i+=this.renderMessage(s)}),t==="human_pending"&&(i+=`
        <div class="aprilo-msg system">
          <div class="aprilo-msg-bubble" style="background: #E0F2FE; color: #0369A1; border: 1px solid #BAE6FD;">
            ⏳ Connecting you with a support specialist. Please hold on...
          </div>
        </div>
      `),t==="human"&&!e.some(s=>s.content.includes("connected with a support agent")||s.content.includes("connected with a support specialist"))&&(i+=`
        <div class="aprilo-msg system">
          <div class="aprilo-msg-bubble">
            ✨ You're now connected with a support specialist.
          </div>
        </div>
      `),this.element.innerHTML=i,this.element.querySelectorAll('[data-action="live-agent"], .aprilo-btn-liveagent').forEach(s=>{s.onclick=r=>{r.preventDefault();const o=s.getAttribute("data-name")||"",a=s.getAttribute("data-email")||"",l=s.getAttribute("data-reason")||"";window.ApriloConnectLiveAgent&&window.ApriloConnectLiveAgent(o,a,l)}}),this.scrollToBottom()}renderRichContent(e){if(!e)return"";let t="",i=e;if(e.includes('<div class="aprilo-action-buttons"')){const c=e.split('<div class="aprilo-action-buttons"');i=c[0],t='<div class="aprilo-action-buttons"'+c.slice(1).join('<div class="aprilo-action-buttons"')}let s=ie(i.trim());s=s.replace(/\*\*(.*?)\*\*/g,"<strong>$1</strong>"),s=s.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)/g,'<a href="$2" target="_blank" rel="noopener noreferrer" class="aprilo-link">$1</a>'),s=s.replace(/(^|[^"'])(https?:\/\/[^\s<]+)/g,'$1<a href="$2" target="_blank" rel="noopener noreferrer" class="aprilo-link">$2</a>');const r=s.split(`
`);let o=!1,a=!1;const l=[];for(let c=0;c<r.length;c++){const h=r[c].trim(),u=h.match(/^([•\-\*])\s+(.*)$/),p=h.match(/^(\d+)\.\s+(.*)$/);u?(a&&(l.push("</ol>"),a=!1),o||(l.push('<ul class="aprilo-list">'),o=!0),l.push(`<li>${u[2]}</li>`)):p?(o&&(l.push("</ul>"),o=!1),a||(l.push('<ol class="aprilo-list">'),a=!0),l.push(`<li>${p[2]}</li>`)):(o&&(l.push("</ul>"),o=!1),a&&(l.push("</ol>"),a=!1),h.length>0&&l.push(`<p class="aprilo-p">${h}</p>`))}return o&&l.push("</ul>"),a&&l.push("</ol>"),l.join("")+t}renderMessage(e){var o,a,l,c,d,h;const t=e.sender_type==="agent"?"human":e.sender_type,i=Qo(e.created_at);if(e.message_type==="whatsapp_redirect")return`
        <div class="aprilo-msg assistant aprilo-wa-redirect-msg" style="width: 100%; max-width: 90%;">
          <div class="aprilo-msg-bubble" style="background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; padding: 12px 14px; border-radius: 14px 14px 14px 3px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; font-weight: 700; font-size: 13px; color: #15803d;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="#25D366" style="flex-shrink:0;"><path d="M20.52 3.48A11.93 11.93 0 0012.04 0C5.46 0 .11 5.35.11 11.93c0 2.1.55 4.15 1.6 5.96L0 24l6.29-1.65a11.9 11.9 0 005.75 1.47h.01c6.58 0 11.93-5.35 11.93-11.93 0-3.19-1.24-6.19-3.46-8.41zM12.04 21.8c-1.78 0-3.52-.48-5.04-1.39l-.36-.21-3.74.98 1-3.64-.23-.37a9.88 9.88 0 01-1.52-5.24c0-5.48 4.46-9.94 9.94-9.94 2.65 0 5.15 1.03 7.03 2.91a9.89 9.89 0 012.91 7.03c0 5.48-4.46 9.94-9.94 9.94z"/></svg>
              <span>Connecting to WhatsApp...</span>
            </div>
            <p style="margin: 0 0 10px 0; font-size: 12px; line-height: 1.45; color: #166534;">
              Opening our official WhatsApp support chat with your enquiry. If your browser blocked the window, please click below:
            </p>
            <a href="${((o=e.metadata)==null?void 0:o.whatsapp_url)||((a=e.metadata)==null?void 0:a.redirect_url)||""}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 6px; background: #25D366; color: #FFFFFF; padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; text-decoration: none; box-shadow: 0 2px 4px rgba(37,211,102,0.25);">
              <span>Open in WhatsApp</span>
              <span>&rarr;</span>
            </a>
          </div>
          ${i?`<div class="aprilo-msg-meta">${i}</div>`:""}
        </div>
      `;const s=this.renderRichContent(e.content);let r="";if(t==="human"&&e.sender_name)r=`<div class="aprilo-msg-meta">${ie(e.sender_name)} &bull; ${i}</div>`;else if(t==="assistant"){const u=e.sender_name||((c=(l=this.state.config)==null?void 0:l.ai)==null?void 0:c.display_name)||((h=(d=this.state.config)==null?void 0:d.widget)==null?void 0:h.name)||"Support AI";r=`<div class="aprilo-msg-meta">${ie(u)} &bull; ${i}</div>`}else i&&(r=`<div class="aprilo-msg-meta">${i}</div>`);return`
      <div class="aprilo-msg ${t}">
        <div class="aprilo-msg-bubble">${s}</div>
        ${r}
      </div>
    `}}class Zo{constructor(e){m(this,"element");this.state=e,this.element=document.createElement("div"),this.element.className="aprilo-typing",this.element.style.display="none",this.element.innerHTML=`
      <div class="aprilo-typing-dot"></div>
      <div class="aprilo-typing-dot"></div>
      <div class="aprilo-typing-dot"></div>
    `,this.state.subscribe(t=>{t==="typing_change"&&(this.element.style.display=this.state.isTyping?"flex":"none")})}getElement(){return this.element}}class ea{constructor(e){m(this,"element");this.state=e,this.element=document.createElement("div"),this.element.className="aprilo-offline-banner",this.element.style.display="none",this.state.subscribe(t=>{t==="config_change"&&this.render()}),this.render()}getElement(){return this.element}render(){const e=this.state.config;if(!e)return;if(e.support.is_online===!1){const i=e.widget.offline_message||"Our team is currently offline. Please leave your message and we will respond as soon as we return.";this.element.innerHTML=`
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span>${ie(i)}</span>
      `,this.element.style.display="flex"}else this.element.style.display="none"}}class ta{constructor(e,t){m(this,"element");m(this,"inputEl");m(this,"sendBtn");m(this,"brandingEl");this.state=e,this.onSend=t,this.element=document.createElement("footer"),this.element.className="aprilo-footer";const i=document.createElement("div");i.className="aprilo-input-row",this.inputEl=document.createElement("input"),this.inputEl.className="aprilo-input",this.inputEl.type="text",this.inputEl.placeholder="Type your message...",this.inputEl.setAttribute("aria-label","Type your message"),this.sendBtn=document.createElement("button"),this.sendBtn.className="aprilo-send-btn",this.sendBtn.setAttribute("type","button"),this.sendBtn.setAttribute("aria-label","Send message"),this.sendBtn.disabled=!0,this.sendBtn.innerHTML=`
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="22" y1="2" x2="11" y2="13"></line>
        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
      </svg>
    `,i.appendChild(this.inputEl),i.appendChild(this.sendBtn),this.brandingEl=document.createElement("div"),this.brandingEl.className="aprilo-branding",this.brandingEl.innerHTML='Powered by <a href="https://apriloinfotech.com" target="_blank" rel="noopener noreferrer">Aprilo Support</a>',this.element.appendChild(i),this.element.appendChild(this.brandingEl),this.inputEl.addEventListener("input",()=>{this.sendBtn.disabled=!this.inputEl.value.trim()}),this.inputEl.addEventListener("keydown",s=>{s.key==="Enter"&&!s.shiftKey&&(s.preventDefault(),this.handleSend())}),this.sendBtn.addEventListener("click",()=>{this.handleSend()}),this.state.subscribe(s=>{var r,o;if(s==="config_change"){const a=((o=(r=this.state.config)==null?void 0:r.behavior)==null?void 0:o.show_branding)??!0;this.brandingEl.style.display=a?"block":"none"}})}getElement(){return this.element}focus(){setTimeout(()=>this.inputEl.focus(),150)}handleSend(){const e=this.inputEl.value.trim();e&&(this.inputEl.value="",this.sendBtn.disabled=!0,this.onSend(e))}}class na{constructor(e,t){m(this,"element");m(this,"header");m(this,"welcome");m(this,"messages");m(this,"typing");m(this,"offline");m(this,"input");m(this,"body");this.state=e,this.element=document.createElement("div"),this.element.className="aprilo-panel",this.element.setAttribute("role","dialog"),this.element.setAttribute("aria-modal","true"),this.element.setAttribute("aria-label","Support Chat Window"),this.header=new Yo(this.state),this.element.appendChild(this.header.getElement()),this.body=document.createElement("div"),this.body.className="aprilo-body",this.body.setAttribute("role","region"),this.body.setAttribute("aria-live","polite"),this.welcome=new Jo(this.state,i=>t.onQuickAction(i)),this.messages=new Xo(this.state),this.typing=new Zo(this.state),this.offline=new ea(this.state),this.body.appendChild(this.welcome.getElement()),this.body.appendChild(this.offline.getElement()),this.body.appendChild(this.messages.getElement()),this.body.appendChild(this.typing.getElement()),this.element.appendChild(this.body),this.input=new ta(this.state,i=>t.onSend(i)),this.element.appendChild(this.input.getElement()),this.element.addEventListener("keydown",i=>{i.key==="Escape"&&this.state.isOpen&&(i.stopPropagation(),this.state.setOpen(!1))}),this.state.subscribe(i=>{i==="open_change"?this.state.isOpen?(this.element.classList.add("open"),this.input.focus(),this.messages.scrollToBottom()):this.element.classList.remove("open"):i==="config_change"?this.applyConfig():i==="messages_change"&&this.state.messages.length>0&&this.welcome.setVisible(!1)})}getElement(){return this.element}applyConfig(){const e=this.state.config;e&&(e.widget.position==="bottom-left"?this.element.classList.add("position-bottom-left"):this.element.classList.remove("position-bottom-left"))}}const ce={widget:{id:"WIDGET_DEFAULT",name:"Aprilo Assistant",display_name:"Chat with Aprilo Support",avatar_url:"",welcome_message:"Hi! How can we assist you with Aprilo Support today?",offline_message:"Our team is currently offline. Please leave your message and we will respond shortly.",position:"bottom-right",enabled:!0},theme:{primary_color:"#22C55E",secondary_color:"#FF8A00",background_color:"#F8FAFC",text_color:"#1F2937",border_radius:"16px"},behavior:{auto_open:!1,show_launcher:!0,show_branding:!0,human_support_enabled:!0},ai:{enabled:!0,agent_id:"AI_SUPPORT_01",display_name:"Aprilo Support AI"},support:{enabled:!0,business_hours_enabled:!1,is_online:!0},quick_actions:[{id:"magento",label:"Magento 2 Solutions",message:"Tell me about your Magento 2 AI and checkout solutions."},{id:"support_trial",label:"1-Month Free Trial*",message:"How does the 1-Month Free Trial for Aprilo Support AI work?"},{id:"agent",label:"Talk to Support Agent",message:"I would like to talk directly with a live support agent."}],organization:{id:"01a0ca0a-90e0-7257-a23c-4d5c723aff99",name:"Aprilo Infotech"},firebase:{apiKey:"AIzaSyDU5Ce2X5w35sZH81e5nX9i41xXj6YXoMg",databaseURL:"https://aprilo-infotech-default-rtdb.firebaseio.com",projectId:"aprilo-infotech",appId:"1:113916326733:web:5659980b28225e34b6fd04"}};async function ia(n,e){const t=`${n.replace(/\/+$/,"")}/api/widget/config?widget_key=${encodeURIComponent(e)}`;y.debug("API",`Fetching configuration from ${t}`);try{const i=new AbortController,s=setTimeout(()=>i.abort(),8e3),r=await fetch(t,{method:"GET",headers:{Accept:"application/json"},signal:i.signal});if(clearTimeout(s),r.ok){const o=await r.json();if(o.success&&o.widget&&o.theme)return y.info("API","Configuration loaded successfully from backend"),{widget:{...ce.widget,...o.widget},theme:{...ce.theme,...o.theme},behavior:{...ce.behavior,...o.behavior},ai:{...ce.ai,...o.ai},support:{...ce.support,...o.support},quick_actions:o.quick_actions||ce.quick_actions,organization:o.organization||ce.organization,firebase:o.firebase||ce.firebase}}y.warn("API",`Backend returned status ${r.status}. Using tenant fallback configuration.`)}catch(i){y.warn("API","Failed to reach backend configuration endpoint. Employing default resilient configuration.",i)}return{...ce,organization:{id:"01a0ca0a-90e0-7257-a23c-4d5c723aff99",name:e.includes("demo")?"Demo Corporation":"Aprilo Infotech"}}}async function sa(n,e,t,i="ai_support"){const s=new URLSearchParams({widget_key:e,mode:i}),r=`${n.replace(/\/+$/,"")}/api/widget/conversations?${s.toString()}`;y.debug("API",`Creating conversation at ${r} with mode ${i}`);try{const l=new AbortController,c=setTimeout(()=>l.abort(),8e3),d=await fetch(r,{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json","X-Widget-Key":e},body:JSON.stringify({widget_key:e,session_id:t,mode:i,domain:window.location.hostname,page_url:window.location.href,page_title:document.title}),signal:l.signal});if(clearTimeout(c),d.ok){const h=await d.json();if(h.success&&h.conversation_id)return{id:h.conversation_id,customer_id:h.customer_id,session_id:t,mode:h.mode||(i==="agent"?"human":"ai"),status:"open",created_at:new Date().toISOString()}}}catch(l){y.warn("API","Backend conversation creation failed. Initializing local resilient session.",l)}const o=`CONV_${Date.now().toString(36).toUpperCase()}`,a=`CUS_${Math.random().toString(36).substring(2,8).toUpperCase()}`;return{id:o,customer_id:a,session_id:t,mode:i==="agent"?"human":"ai",status:"open",created_at:new Date().toISOString()}}async function es(n,e,t,i="ai_support"){var a,l;const s=((a=t.metadata)==null?void 0:a.widget_key)||"",r=new URLSearchParams({widget_key:s,mode:i}),o=`${n.replace(/\/+$/,"")}/api/widget/conversations/${e}/messages?${r.toString()}`;y.debug("API",`Sending message to ${o} with mode ${i}`,t);try{const c=new AbortController,d=setTimeout(()=>c.abort(),1e4),h=await fetch(o,{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json",...s?{"X-Widget-Key":s}:{}},body:JSON.stringify({widget_key:s,mode:i,message:t.message,message_type:t.message_type||"text",metadata:t.metadata||{}}),signal:c.signal});if(clearTimeout(d),h.ok)return await h.json();y.warn("API",`Conversation message endpoint returned ${h.status}. Falling back to /api/widget/chat.`)}catch(c){y.warn("API","Backend conversation message send call failed. Falling back to /api/widget/chat.",c)}try{const c=new URLSearchParams({widget_key:s,mode:i}),d=`${n.replace(/\/+$/,"")}/api/widget/chat?${c.toString()}`,h=((l=t.metadata)==null?void 0:l.session_id)||e,u=await fetch(d,{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json",...s?{"X-Widget-Key":s}:{}},body:JSON.stringify({widget_key:s,mode:i,session_id:h,question_text:t.message,message:t.message})});if(u.ok){const p=await u.json();return{success:!0,message_id:p.question_id||`MSG_${Date.now().toString(36).toUpperCase()}`,mode:i==="agent"?"human":"ai",reply:p.answer_text?{id:p.question_id||`bot_${Date.now()}`,sender:"assistant",content:p.answer_text,timestamp:new Date().toISOString()}:void 0}}}catch(c){y.warn("API","Direct chat fallback also failed.",c)}return{success:!0,message_id:`MSG_${Date.now().toString(36).toUpperCase()}`}}class ra{constructor(){m(this,"isConnected",!1);m(this,"unsubscribeMessages");m(this,"unsubscribeConversation")}async connect(e){if(!this.isConnected){if(!e.firebaseConfig||!e.firebaseConfig.databaseURL){y.debug("Firebase","No Firebase configuration provided for tenant. Realtime sync skipped.");return}try{y.debug("Firebase",`Initializing Realtime Database for tenant ${e.organizationId}`);const{initializeApp:t,getApps:i}=await Promise.resolve().then(()=>Ql),{getDatabase:s,ref:r,onChildAdded:o,onValue:a,off:l}=await Promise.resolve().then(()=>Vu),c=i().find(p=>p.name==="aprilo-widget")||t(e.firebaseConfig,"aprilo-widget"),d=s(c),h=r(d,`tenants/${e.organizationId}/messages/${e.conversationId}`);o(h,p=>{const _=p.val();_&&e.onMessage({id:p.key||`MSG_${Date.now()}`,conversation_id:e.conversationId,sender_type:_.sender_type||"assistant",message_type:_.message_type||"text",content:_.content||_.message||"",sender_name:_.sender_name,created_at:_.created_at||new Date().toISOString(),status:"delivered"})});const u=r(d,`tenants/${e.organizationId}/conversations/${e.conversationId}`);a(u,p=>{const _=p.val();_&&(_.mode&&e.onModeChange(_.mode),typeof _.is_typing=="boolean"&&e.onTyping(_.is_typing))}),this.unsubscribeMessages=()=>l(h),this.unsubscribeConversation=()=>l(u),this.isConnected=!0,y.info("Firebase","Connected to Realtime Database successfully")}catch(t){y.warn("Firebase","Unable to establish Firebase Realtime connection. Message polling/HTTP fallback active.",t)}}}disconnect(){this.unsubscribeMessages&&(this.unsubscribeMessages(),this.unsubscribeMessages=void 0),this.unsubscribeConversation&&(this.unsubscribeConversation(),this.unsubscribeConversation=void 0),this.isConnected=!1}}class oa{constructor(){m(this,"memoryStore",new Map);m(this,"isStorageAvailable",!0);m(this,"SESSION_TTL_SECONDS",10800);try{const e="__aprilo_storage_test__";window.sessionStorage.setItem(e,"1"),window.sessionStorage.removeItem(e)}catch(e){this.isStorageAvailable=!1,y.warn("Core","sessionStorage unavailable or blocked. Falling back to memory storage.",e)}}getCookie(e){if(typeof document>"u")return null;try{const t=document.cookie.split(";");for(let i=0;i<t.length;i++){const s=t[i].trim(),r=s.indexOf("=");if(r!==-1&&decodeURIComponent(s.substring(0,r))===e)return decodeURIComponent(s.substring(r+1))}}catch(t){y.warn("Core","Error reading cookie:",t)}return null}setCookie(e,t,i=this.SESSION_TTL_SECONDS){if(!(typeof document>"u"))try{const s=new Date(Date.now()+i*1e3).toUTCString();document.cookie=`${encodeURIComponent(e)}=${encodeURIComponent(t)}; expires=${s}; max-age=${i}; path=/; SameSite=Lax`}catch(s){y.warn("Core","Error writing cookie:",s)}}removeCookie(e){if(!(typeof document>"u"))try{document.cookie=`${encodeURIComponent(e)}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; max-age=0; path=/; SameSite=Lax`}catch(t){y.warn("Core","Error clearing cookie:",t)}}getItem(e){if(this.isStorageAvailable)try{return window.sessionStorage.getItem(e)}catch{return this.memoryStore.get(e)||null}return this.memoryStore.get(e)||null}setItem(e,t){if(this.isStorageAvailable)try{window.sessionStorage.setItem(e,t);return}catch{}this.memoryStore.set(e,t)}removeItem(e){if(this.isStorageAvailable)try{window.sessionStorage.removeItem(e)}catch{}this.memoryStore.delete(e)}getOrCreateSessionId(e){const t=`aprilo_session_${e}`;let i=this.getCookie(t);if(i)return this.setCookie(t,i,this.SESSION_TTL_SECONDS),i;this.removeItem(`aprilo_conv_${e}`);try{window.localStorage.removeItem(`aprilo_session_${e}`),window.localStorage.removeItem(`aprilo_conv_${e}`)}catch{}return i=`sess_${Date.now()}_${Math.random().toString(36).substring(2,9)}`,this.setCookie(t,i,this.SESSION_TTL_SECONDS),y.info("Core",`Started new session: ${i} (cookie valid for 3 hours)`),i}getConversationId(e){const t=`aprilo_session_${e}`;return this.getCookie(t)?this.getItem(`aprilo_conv_${e}`):null}setConversationId(e,t){const i=`aprilo_session_${e}`,s=this.getCookie(i);s&&this.setCookie(i,s,this.SESSION_TTL_SECONDS),this.setItem(`aprilo_conv_${e}`,t)}clearSession(e){this.removeCookie(`aprilo_session_${e}`),this.removeItem(`aprilo_conv_${e}`);try{window.localStorage.removeItem(`aprilo_session_${e}`),window.localStorage.removeItem(`aprilo_conv_${e}`)}catch{}}}const at=new oa,aa=':host{all:initial;--aprilo-primary: #22C55E;--aprilo-primary-dark: #16A34A;--aprilo-primary-deep: #065F46;--aprilo-accent: #FF8A00;--aprilo-accent-light: #FFB020;--aprilo-background: #F8FAFC;--aprilo-panel-bg: #FFFFFF;--aprilo-text: #1F2937;--aprilo-secondary-text: #6B7280;--aprilo-border: #E2E8F0;--aprilo-border-radius: 16px;--aprilo-shadow: 0 12px 36px -4px rgba(0, 0, 0, .15), 0 4px 16px -2px rgba(0, 0, 0, .08);--aprilo-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";--aprilo-z-index: 2147483640;font-family:var(--aprilo-font-family);font-size:14px;line-height:1.5;color:var(--aprilo-text);box-sizing:border-box;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}*,*:before,*:after{box-sizing:border-box;margin:0;padding:0}.aprilo-launcher{position:fixed;bottom:24px;right:24px;width:60px;height:60px;border-radius:50%;background:var(--aprilo-primary);color:#fff;box-shadow:0 8px 24px #22c55e59,0 2px 8px #0000001a;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:var(--aprilo-z-index);transition:transform .22s cubic-bezier(.34,1.56,.64,1),box-shadow .2s ease,background-color .2s ease;outline:none;touch-action:manipulation}.aprilo-launcher:hover{transform:scale(1.06);background:var(--aprilo-primary-dark);box-shadow:0 10px 28px #16a34a6b,0 4px 12px #0000001f}.aprilo-launcher:focus-visible{box-shadow:0 0 0 3px #22c55e66,0 8px 24px #0003}.aprilo-launcher:active{transform:scale(.96)}.aprilo-launcher.position-bottom-left{right:auto;left:24px}.aprilo-launcher-icon{width:30px;height:30px;display:flex;align-items:center;justify-content:center;transition:transform .25s ease,opacity .2s ease}.aprilo-launcher-badge{position:absolute;top:-2px;right:-2px;min-width:20px;height:20px;padding:0 6px;border-radius:10px;background:var(--aprilo-accent);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;border:2px solid #FFFFFF;box-shadow:0 2px 6px #ff8a0066;animation:aprilo-pulse 2s infinite}@keyframes aprilo-pulse{0%{transform:scale(1)}50%{transform:scale(1.1)}to{transform:scale(1)}}.aprilo-panel{position:fixed;bottom:96px;right:24px;width:380px;max-width:calc(100vw - 32px);height:620px;max-height:calc(100vh - 120px);background:var(--aprilo-panel-bg);border-radius:var(--aprilo-border-radius);box-shadow:var(--aprilo-shadow);display:flex;flex-direction:column;overflow:hidden;z-index:var(--aprilo-z-index);border:1px solid rgba(0,0,0,.08);opacity:0;visibility:hidden;transform:translateY(18px) scale(.96);transform-origin:bottom right;transition:opacity .24s cubic-bezier(.16,1,.3,1),transform .24s cubic-bezier(.16,1,.3,1),visibility .24s}.aprilo-panel.position-bottom-left{right:auto;left:24px;transform-origin:bottom left}.aprilo-panel.open{opacity:1;visibility:visible;transform:translateY(0) scale(1)}.aprilo-header{background:linear-gradient(135deg,var(--aprilo-primary),var(--aprilo-primary-dark));color:#fff;padding:16px 18px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,.1);flex-shrink:0}.aprilo-header-info{display:flex;align-items:center;gap:12px}.aprilo-header-avatar{width:40px;height:40px;border-radius:50%;background:#fff3;border:2px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}.aprilo-header-avatar img{width:100%;height:100%;object-fit:cover}.aprilo-header-avatar svg{width:22px;height:22px;fill:#fff}.aprilo-header-titles{display:flex;flex-direction:column}.aprilo-header-name{font-size:15px;font-weight:600;letter-spacing:-.01em;color:#fff}.aprilo-header-status{display:flex;align-items:center;gap:6px;font-size:12px;color:#ffffffe6}.aprilo-status-dot{width:8px;height:8px;border-radius:50%;background:#a7f3d0;box-shadow:0 0 8px #34d399}.aprilo-header-actions{display:flex;align-items:center;gap:6px}.aprilo-header-btn{background:#ffffff26;border:none;color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s ease,transform .15s ease;outline:none}.aprilo-header-btn:hover{background:#ffffff47;transform:scale(1.05)}.aprilo-header-btn:focus-visible{box-shadow:0 0 0 2px #fff}.aprilo-body{flex:1;background:var(--aprilo-background);overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px;scroll-behavior:smooth}.aprilo-welcome-card{background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 8px #0000000a;border:1px solid var(--aprilo-border);margin-bottom:8px}.aprilo-welcome-heading{font-size:15px;font-weight:600;color:var(--aprilo-text);margin-bottom:6px}.aprilo-welcome-text{font-size:13px;color:var(--aprilo-secondary-text);line-height:1.5}.aprilo-quick-actions{display:flex;flex-direction:column;gap:8px;margin-top:12px}.aprilo-quick-btn{background:#fff;border:1px solid var(--aprilo-border);border-radius:8px;padding:10px 14px;font-size:13px;font-weight:500;color:var(--aprilo-text);text-align:left;cursor:pointer;transition:all .18s ease;display:flex;align-items:center;justify-content:space-between}.aprilo-quick-btn:hover{background:#f0fdf4;border-color:var(--aprilo-primary);color:var(--aprilo-primary-dark);transform:translate(2px)}.aprilo-quick-btn-arrow{color:var(--aprilo-secondary-text);font-size:14px;transition:transform .15s ease}.aprilo-quick-btn:hover .aprilo-quick-btn-arrow{transform:translate(3px);color:var(--aprilo-primary)}.aprilo-msg{display:flex;flex-direction:column;max-width:82%;word-break:break-word;animation:aprilo-fade-in .2s ease-out}@keyframes aprilo-fade-in{0%{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}.aprilo-msg.user,.aprilo-msg.customer{align-self:flex-end;align-items:flex-end}.aprilo-msg.assistant,.aprilo-msg.human{align-self:flex-start;align-items:flex-start}.aprilo-msg-bubble{padding:10px 14px;border-radius:14px;font-size:13.5px;line-height:1.45}.aprilo-msg.user .aprilo-msg-bubble,.aprilo-msg.customer .aprilo-msg-bubble{background:var(--aprilo-primary);color:#fff;border-bottom-right-radius:4px}.aprilo-msg.assistant .aprilo-msg-bubble{background:#fff;color:var(--aprilo-text);border:1px solid var(--aprilo-border);border-bottom-left-radius:4px;box-shadow:0 1px 3px #0000000a}.aprilo-msg.human .aprilo-msg-bubble{background:#eff6ff;color:#1e3a8a;border:1px solid #BFDBFE;border-bottom-left-radius:4px}.aprilo-msg.system{align-self:center;align-items:center;max-width:90%;margin:6px 0}.aprilo-msg.system .aprilo-msg-bubble{background:#fef3c7;color:#92400e;border:1px solid #FDE68A;font-size:12px;padding:6px 12px;border-radius:20px;text-align:center}.aprilo-msg-meta{font-size:10.5px;color:var(--aprilo-secondary-text);margin-top:4px;padding:0 4px}.aprilo-typing{display:flex;align-items:center;gap:4px;padding:10px 14px;background:#fff;border-radius:14px 14px 14px 4px;border:1px solid var(--aprilo-border);width:fit-content;align-self:flex-start}.aprilo-typing-dot{width:6px;height:6px;border-radius:50%;background:var(--aprilo-secondary-text);animation:aprilo-bounce 1.3s infinite ease-in-out}.aprilo-typing-dot:nth-child(1){animation-delay:0s}.aprilo-typing-dot:nth-child(2){animation-delay:.2s}.aprilo-typing-dot:nth-child(3){animation-delay:.4s}@keyframes aprilo-bounce{0%,80%,to{transform:scale(.6);opacity:.4}40%{transform:scale(1.1);opacity:1}}.aprilo-offline-banner{background:#fffbeb;border:1px solid #FDE68A;border-radius:10px;padding:12px;font-size:12.5px;color:#92400e;display:flex;align-items:flex-start;gap:8px;margin-top:8px}.aprilo-footer{background:#fff;padding:12px 14px;border-top:1px solid var(--aprilo-border);display:flex;flex-direction:column;gap:6px;flex-shrink:0}.aprilo-input-row{display:flex;align-items:center;gap:8px}.aprilo-input{flex:1;background:var(--aprilo-background);border:1px solid var(--aprilo-border);border-radius:20px;padding:10px 16px;font-family:inherit;font-size:13.5px;color:var(--aprilo-text);outline:none;transition:border-color .15s ease,box-shadow .15s ease;resize:none}.aprilo-input:focus{border-color:var(--aprilo-primary);box-shadow:0 0 0 2px #22c55e26;background:#fff}.aprilo-input::placeholder{color:#9ca3af}.aprilo-send-btn{width:38px;height:38px;border-radius:50%;background:var(--aprilo-primary);border:none;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .15s ease,background-color .15s ease,opacity .15s ease;flex-shrink:0;outline:none}.aprilo-send-btn:hover:not(:disabled){background:var(--aprilo-primary-dark);transform:scale(1.05)}.aprilo-send-btn:disabled{opacity:.45;cursor:not-allowed}.aprilo-branding{text-align:center;font-size:11px;color:#9ca3af;letter-spacing:.01em}.aprilo-branding a{color:var(--aprilo-secondary-text);text-decoration:none;font-weight:500}.aprilo-branding a:hover{color:var(--aprilo-primary)}@media(max-width:640px){.aprilo-panel{top:0;left:0;right:0;bottom:0;width:100vw;max-width:100vw;height:100vh;height:100dvh;max-height:100dvh;border-radius:0;border:none;box-shadow:none;transform:translateY(100%)}.aprilo-panel.open{transform:translateY(0)}.aprilo-header{padding-top:max(16px,env(safe-area-inset-top))}.aprilo-footer{padding-bottom:max(12px,env(safe-area-inset-bottom))}.aprilo-launcher{bottom:max(20px,env(safe-area-inset-bottom));right:max(20px,env(safe-area-inset-right))}.aprilo-launcher.position-bottom-left{left:max(20px,env(safe-area-inset-left))}}.aprilo-action-buttons{display:flex;flex-direction:column;gap:8px;margin-top:10px}.aprilo-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:9px 14px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:all .2s ease;border:1px solid transparent;font-family:inherit;line-height:1.3}.aprilo-btn-whatsapp{background:#25d366;color:#fff!important;border-color:#20bd5a}.aprilo-btn-whatsapp:hover{background:#20bd5a;box-shadow:0 4px 12px #25d36659;transform:translateY(-1px)}.aprilo-btn-liveagent{background:#0284c7;color:#fff!important;border-color:#0369a1}.aprilo-btn-liveagent:hover{background:#0369a1;box-shadow:0 4px 12px #0284c759;transform:translateY(-1px)}.aprilo-msg-bubble p.aprilo-p{margin:0 0 6px}.aprilo-msg-bubble p.aprilo-p:last-child{margin-bottom:0}.aprilo-msg-bubble ul.aprilo-list,.aprilo-msg-bubble ol.aprilo-list{margin:6px 0;padding-left:20px}.aprilo-msg-bubble ul.aprilo-list li,.aprilo-msg-bubble ol.aprilo-list li{margin-bottom:4px;line-height:1.4}.aprilo-msg-bubble ul.aprilo-list li:last-child,.aprilo-msg-bubble ol.aprilo-list li:last-child{margin-bottom:0}.aprilo-msg-bubble a.aprilo-link{color:var(--aprilo-primary, #0284c7);text-decoration:underline;word-break:break-all;transition:opacity .2s ease}.aprilo-msg.user .aprilo-msg-bubble a.aprilo-link{color:#fff;text-decoration:underline}.aprilo-msg-bubble a.aprilo-link:hover{opacity:.8}';class Tn{constructor(e){m(this,"host");m(this,"shadowRoot");m(this,"state");m(this,"launcher");m(this,"panel");m(this,"realtime");m(this,"options");m(this,"isDestroyed",!1);m(this,"initPromise");this.options={apiUrl:e.apiUrl||"https://app.apriloinfotech.com",autoOpen:e.autoOpen??!1,debug:e.debug??!1,...e},y.setDebug(!!this.options.debug),y.info("Core",`Initializing Aprilo Chat Widget for key: ${this.options.widgetKey}`),this.state=new qo,this.realtime=new ra,this.host=document.createElement("div"),this.host.id="aprilo-chat-widget-root",this.shadowRoot=this.host.attachShadow({mode:"open"});const t=document.createElement("style");t.textContent=aa,this.shadowRoot.appendChild(t),this.launcher=new Ko(this.state),this.panel=new na(this.state,{onSend:i=>this.handleSendMessage(i),onQuickAction:i=>this.handleQuickAction(i)}),this.shadowRoot.appendChild(this.launcher.getElement()),this.shadowRoot.appendChild(this.panel.getElement()),document.body?document.body.appendChild(this.host):document.addEventListener("DOMContentLoaded",()=>{document.body.appendChild(this.host)}),this.state.subscribe(i=>{i==="open_change"&&this.state.isOpen&&this.ensureConversationActive()}),this.initPromise=this.loadConfiguration()}async loadConfiguration(){try{const e=await ia(this.options.apiUrl,this.options.widgetKey);this.state.setConfig(e),this.applyTheme(e.theme),(this.options.autoOpen||e.behavior.auto_open)&&setTimeout(()=>{this.isDestroyed||this.state.setOpen(!0)},1200)}catch(e){y.error("Core","Failed to load configuration:",e)}}applyTheme(e){e&&(e.primary_color&&(this.host.style.setProperty("--aprilo-primary",e.primary_color),this.host.style.setProperty("--aprilo-primary-dark",this.adjustColorBrightness(e.primary_color,-18))),e.secondary_color&&this.host.style.setProperty("--aprilo-accent",e.secondary_color),e.background_color&&this.host.style.setProperty("--aprilo-background",e.background_color),e.text_color&&this.host.style.setProperty("--aprilo-text",e.text_color),e.border_radius&&this.host.style.setProperty("--aprilo-border-radius",e.border_radius))}async ensureConversationActive(){var a;const e=`aprilo_session_${this.options.widgetKey}`;if(!at.getCookie(e))(this.state.conversation||this.state.messages.length>0)&&(y.info("Core","Active session cookie not found or expired. Starting brand new chat session."),this.state.clearMessages(),this.state.setConversation(null));else if(this.state.conversation){at.getOrCreateSessionId(this.options.widgetKey);return}const i=this.state.mode==="human"||this.state.mode==="human_pending"?"agent":"ai_support",s=at.getOrCreateSessionId(this.options.widgetKey),r=await sa(this.options.apiUrl,this.options.widgetKey,s,i);this.state.setConversation(r),at.setConversationId(this.options.widgetKey,r.id);const o=this.state.config;(a=o==null?void 0:o.organization)!=null&&a.id&&this.realtime.connect({organizationId:o.organization.id,conversationId:r.id,firebaseConfig:o.firebase,onMessage:l=>{this.state.addMessage(l),(l.sender_type==="human"||l.sender_type==="agent")&&this.state.setMode("human")},onTyping:l=>this.state.setTyping(l),onModeChange:l=>this.state.setMode(l)})}async connectLiveAgent(e,t,i){this.state.setMode("human_pending"),await this.ensureConversationActive();const s=this.state.conversation;if(s){this.state.addMessage({id:`sys_${Date.now()}`,conversation_id:s.id,sender_type:"system",message_type:"text",content:"Connecting you with a live support agent. Please wait...",created_at:new Date().toISOString(),status:"delivered"});try{const o=`${(this.options.apiUrl||"https://app.apriloinfotech.com").replace(/\/+$/,"")}/api/widget/escalate-live?mode=agent&widget_key=${encodeURIComponent(this.options.widgetKey)}`;await fetch(o,{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json","X-Widget-Key":this.options.widgetKey},body:JSON.stringify({widget_key:this.options.widgetKey,mode:"agent",conversation_id:s.id,customer_name:e||"Valued Customer",customer_email:t||"",reason:i||"Support enquiry"})})}catch(r){y.warn("Core","Escalation API call failed:",r)}}}async handleSendMessage(e){var a,l,c,d,h,u,p,_,C,D,fe,ze,ot,xn,Fo,Bo,$o,Wo,Uo,Ho,zo,Vo;if(!!((l=(a=this.state.config)==null?void 0:a.behavior)!=null&&l.only_whatsapp_widget||(d=(c=this.state.config)==null?void 0:c.widget)!=null&&d.only_whatsapp_widget||(u=(h=this.state.config)==null?void 0:h.whatsapp)!=null&&u.only_whatsapp_widget)){const I=((_=(p=this.state.config)==null?void 0:p.organization)==null?void 0:_.whatsapp_url)||((D=(C=this.state.config)==null?void 0:C.whatsapp)==null?void 0:D.url)||((ze=(fe=this.state.config)==null?void 0:fe.widget)==null?void 0:ze.whatsapp_url),pe=((xn=(ot=this.state.config)==null?void 0:ot.organization)==null?void 0:xn.whatsapp_number)||((Bo=(Fo=this.state.config)==null?void 0:Fo.whatsapp)==null?void 0:Bo.number)||((Wo=($o=this.state.config)==null?void 0:$o.widget)==null?void 0:Wo.whatsapp_number)||"";let ne=I||"";!ne&&pe&&(ne=`https://wa.me/${pe.replace(/[^0-9]/g,"")}`),ne||(ne="https://wa.me/message/BE3K4RAO24GXK1");const ju=ne.includes("?")?"&":"?",Ft=`${ne}${ju}text=${encodeURIComponent(e)}`;try{const Ae=window.open(Ft,"_blank");(!Ae||Ae.closed||typeof Ae.closed>"u")&&(window.location.href=Ft)}catch{try{window.location.href=Ft}catch{}}const Gu={id:`usr_${Date.now()}_${Math.random().toString(36).substring(2,6)}`,conversation_id:((Uo=this.state.conversation)==null?void 0:Uo.id)||"wa_conv",sender_type:"user",message_type:"text",content:e,created_at:new Date().toISOString(),status:"delivered"};this.state.addMessage(Gu);const qu={id:`wa_${Date.now()}_${Math.random().toString(36).substring(2,6)}`,conversation_id:((Ho=this.state.conversation)==null?void 0:Ho.id)||"wa_conv",sender_type:"assistant",message_type:"whatsapp_redirect",content:e,metadata:{whatsapp_url:Ft,redirect_url:Ft},created_at:new Date().toISOString(),status:"delivered"};this.state.addMessage(qu);const Ku=at.getOrCreateSessionId(this.options.widgetKey);try{const jo=`${(this.options.apiUrl||"https://app.apriloinfotech.com").replace(/\/+$/,"")}/api/widget/whatsapp-redirect?mode=whatsapp&widget_key=${encodeURIComponent(this.options.widgetKey)}`,Go=JSON.stringify({widget_key:this.options.widgetKey,mode:"whatsapp",session_id:((zo=this.state.conversation)==null?void 0:zo.session_id)||Ku,conversation_id:(Vo=this.state.conversation)==null?void 0:Vo.id,message:e});typeof navigator<"u"&&navigator.sendBeacon?navigator.sendBeacon(jo,new Blob([Go],{type:"application/json"})):fetch(jo,{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json","X-Widget-Key":this.options.widgetKey},body:Go,keepalive:!0}).catch(()=>{})}catch(Ae){y.warn("Core","Tracking exception:",Ae)}return}await this.ensureConversationActive();const i=this.state.conversation;if(!i)return;const s={id:`usr_${Date.now()}_${Math.random().toString(36).substring(2,6)}`,conversation_id:i.id,sender_type:"user",message_type:"text",content:e,created_at:new Date().toISOString(),status:"sending"};this.state.addMessage(s);const r=this.state.mode==="human"||this.state.mode==="human_pending",o=r?"agent":"ai_support";if(r){try{await es(this.options.apiUrl,i.id,{message:e,message_type:"text",metadata:{widget_key:this.options.widgetKey,session_id:i.session_id,mode:"agent"}},"agent")}catch(I){y.error("Core","Error sending live agent message:",I)}return}this.state.setTyping(!0);try{const I=await es(this.options.apiUrl,i.id,{message:e,message_type:"text",metadata:{widget_key:this.options.widgetKey,session_id:i.session_id,mode:o}},o);this.state.setTyping(!1),I!=null&&I.mode&&this.state.setMode(I.mode),I!=null&&I.reply&&this.state.addMessage({id:I.reply.id||`bot_${Date.now()}`,conversation_id:i.id,sender_type:I.reply.sender==="user"?"user":I.reply.sender==="assistant"?"assistant":"human",message_type:"text",content:I.reply.content,sender_name:I.reply.sender_name||"Aprilo Support AI",created_at:I.reply.timestamp||new Date().toISOString(),status:"delivered"});const pe=(I==null?void 0:I.redirect_url)||(I==null?void 0:I.whatsapp_url);if(pe){this.state.addMessage({id:`wa_${Date.now()}`,conversation_id:i.id,sender_type:"assistant",message_type:"whatsapp_redirect",content:e,metadata:{whatsapp_url:pe,redirect_url:pe},created_at:new Date().toISOString(),status:"delivered"});try{const ne=window.open(pe,"_blank");(!ne||ne.closed||typeof ne.closed>"u")&&(window.location.href=pe)}catch{try{window.location.href=pe}catch{}}}else!(I!=null&&I.reply)&&(this.options.widgetKey.includes("test")||this.options.widgetKey.includes("demo"))&&setTimeout(()=>{this.simulateAssistantReply(e)},500)}catch(I){y.error("Core","Error sending message:",I),this.state.setTyping(!1)}}handleQuickAction(e){this.handleSendMessage(e.message)}simulateAssistantReply(e){var a,l,c,d,h,u,p,_,C;const t=((l=(a=this.state.config)==null?void 0:a.organization)==null?void 0:l.name)||"our organization",i=((d=(c=this.state.config)==null?void 0:c.ai)==null?void 0:d.display_name)||((u=(h=this.state.config)==null?void 0:h.widget)==null?void 0:u.name)||`${t} Assistant`,s=e.toLowerCase();let r=`Thank you for reaching out to ${t}! How can we assist you today?`;s.includes("hi")||s.includes("hello")||s.includes("hey")?r=`Hello! I am ${i}, assistant for ${t}. How can I assist you with our services today?`:s.includes("service")||s.includes("what")||s.includes("offer")?r=`${t} provides professional services and solutions. Feel free to ask about our offerings or request support.`:s.includes("sales")||s.includes("pricing")||s.includes("contact")?r=`I would be happy to connect you with ${t} support. Would you like to leave a message or speak with our team?`:(s.includes("human")||s.includes("agent"))&&(this.state.setMode("human"),r=`I am routing this conversation to ${t} support. A specialist will assist you shortly.`);const o={id:`bot_${Date.now()}`,conversation_id:((p=this.state.conversation)==null?void 0:p.id)||"demo_conv",sender_type:this.state.mode==="human"?"human":"assistant",message_type:"text",content:r,sender_name:this.state.mode==="human"?"Support Agent (Sarah)":((C=(_=this.state.config)==null?void 0:_.ai)==null?void 0:C.display_name)||"Aprilo Support AI",created_at:new Date().toISOString(),status:"delivered"};this.state.addMessage(o)}adjustColorBrightness(e,t){const i=parseInt(e.replace("#",""),16),s=Math.round(2.55*t),r=(i>>16)+s,o=(i>>8&255)+s,a=(i&255)+s;return"#"+(16777216+(r<255?r<1?0:r:255)*65536+(o<255?o<1?0:o:255)*256+(a<255?a<1?0:a:255)).toString(16).slice(1)}open(){this.state.setOpen(!0)}close(){this.state.setOpen(!1)}toggle(){this.state.toggleOpen()}isOpen(){return this.state.isOpen}ready(){return this.initPromise}destroy(){this.isDestroyed=!0,this.realtime.disconnect(),this.state.reset(),this.host.parentElement&&this.host.parentElement.removeChild(this.host),y.info("Core","Aprilo Chat Widget destroyed")}}let N=null;function ts(){try{const n=document.currentScript||document.querySelector("script[data-widget-key]");if(!n){y.debug("Core","No script with data-widget-key found during auto-init.");return}const e=n.getAttribute("data-widget-key");if(!e||!Zi(e)){y.warn("Core",`Invalid or missing data-widget-key on script tag: "${e}".`);return}const t=n.getAttribute("data-auto-open")==="true";let i=n.getAttribute("data-api-url")||void 0;i&&i.includes("localhost")&&typeof window<"u"&&window.location.hostname&&!window.location.hostname.includes("localhost")&&(window.location.hostname.endsWith("apriloinfotech.com")?i=window.location.origin:i="https://app.apriloinfotech.com"),i||(typeof window<"u"&&window.location.hostname&&window.location.hostname.endsWith("apriloinfotech.com")?i=window.location.origin:i="https://app.apriloinfotech.com");const s=n.getAttribute("data-debug")==="true";N=new Tn({widgetKey:e,autoOpen:t,apiUrl:i||"https://aprilo-infotech.web.app",debug:s})}catch(n){y.error("Core","Auto-initialization exception:",n)}}const ns={version:"1.0.0",connectLiveAgent:(n,e,t)=>N?N.connectLiveAgent(n,e,t):Promise.resolve(),open:()=>N==null?void 0:N.open(),close:()=>N==null?void 0:N.close(),toggle:()=>N==null?void 0:N.toggle(),isOpen:()=>!!(N!=null&&N.isOpen()),destroy:()=>{N==null||N.destroy(),N=null},init:n=>!n.widgetKey||!Zi(n.widgetKey)?(y.error("Core",`Cannot initialize widget: invalid key "${n.widgetKey}"`),null):(N&&N.destroy(),N=new Tn(n),N)};typeof window<"u"&&(window.ApriloChat=ns,window.ApriloConnectLiveAgent=(n,e,t)=>{N&&N.connectLiveAgent(n,e,t)},document.readyState==="loading"?document.addEventListener("DOMContentLoaded",ts):ts());const la=()=>{};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const is={NODE_ADMIN:!1,SDK_VERSION:"${JSCORE_VERSION}"};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const f=function(n,e){if(!n)throw Ve(e)},Ve=function(n){return new Error("Firebase Database ("+is.SDK_VERSION+") INTERNAL ASSERT FAILED: "+n)};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const ss=function(n){const e=[];let t=0;for(let i=0;i<n.length;i++){let s=n.charCodeAt(i);s<128?e[t++]=s:s<2048?(e[t++]=s>>6|192,e[t++]=s&63|128):(s&64512)===55296&&i+1<n.length&&(n.charCodeAt(i+1)&64512)===56320?(s=65536+((s&1023)<<10)+(n.charCodeAt(++i)&1023),e[t++]=s>>18|240,e[t++]=s>>12&63|128,e[t++]=s>>6&63|128,e[t++]=s&63|128):(e[t++]=s>>12|224,e[t++]=s>>6&63|128,e[t++]=s&63|128)}return e},ca=function(n){const e=[];let t=0,i=0;for(;t<n.length;){const s=n[t++];if(s<128)e[i++]=String.fromCharCode(s);else if(s>191&&s<224){const r=n[t++];e[i++]=String.fromCharCode((s&31)<<6|r&63)}else if(s>239&&s<365){const r=n[t++],o=n[t++],a=n[t++],l=((s&7)<<18|(r&63)<<12|(o&63)<<6|a&63)-65536;e[i++]=String.fromCharCode(55296+(l>>10)),e[i++]=String.fromCharCode(56320+(l&1023))}else{const r=n[t++],o=n[t++];e[i++]=String.fromCharCode((s&15)<<12|(r&63)<<6|o&63)}}return e.join("")},kn={byteToCharMap_:null,charToByteMap_:null,byteToCharMapWebSafe_:null,charToByteMapWebSafe_:null,ENCODED_VALS_BASE:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",get ENCODED_VALS(){return this.ENCODED_VALS_BASE+"+/="},get ENCODED_VALS_WEBSAFE(){return this.ENCODED_VALS_BASE+"-_."},HAS_NATIVE_SUPPORT:typeof atob=="function",encodeByteArray(n,e){if(!Array.isArray(n))throw Error("encodeByteArray takes an array as a parameter");this.init_();const t=e?this.byteToCharMapWebSafe_:this.byteToCharMap_,i=[];for(let s=0;s<n.length;s+=3){const r=n[s],o=s+1<n.length,a=o?n[s+1]:0,l=s+2<n.length,c=l?n[s+2]:0,d=r>>2,h=(r&3)<<4|a>>4;let u=(a&15)<<2|c>>6,p=c&63;l||(p=64,o||(u=64)),i.push(t[d],t[h],t[u],t[p])}return i.join("")},encodeString(n,e){return this.HAS_NATIVE_SUPPORT&&!e?btoa(n):this.encodeByteArray(ss(n),e)},decodeString(n,e){return this.HAS_NATIVE_SUPPORT&&!e?atob(n):ca(this.decodeStringToByteArray(n,e))},decodeStringToByteArray(n,e){this.init_();const t=e?this.charToByteMapWebSafe_:this.charToByteMap_,i=[];for(let s=0;s<n.length;){const r=t[n.charAt(s++)],a=s<n.length?t[n.charAt(s)]:0;++s;const c=s<n.length?t[n.charAt(s)]:64;++s;const h=s<n.length?t[n.charAt(s)]:64;if(++s,r==null||a==null||c==null||h==null)throw new ha;const u=r<<2|a>>4;if(i.push(u),c!==64){const p=a<<4&240|c>>2;if(i.push(p),h!==64){const _=c<<6&192|h;i.push(_)}}}return i},init_(){if(!this.byteToCharMap_){this.byteToCharMap_={},this.charToByteMap_={},this.byteToCharMapWebSafe_={},this.charToByteMapWebSafe_={};for(let n=0;n<this.ENCODED_VALS.length;n++)this.byteToCharMap_[n]=this.ENCODED_VALS.charAt(n),this.charToByteMap_[this.byteToCharMap_[n]]=n,this.byteToCharMapWebSafe_[n]=this.ENCODED_VALS_WEBSAFE.charAt(n),this.charToByteMapWebSafe_[this.byteToCharMapWebSafe_[n]]=n,n>=this.ENCODED_VALS_BASE.length&&(this.charToByteMap_[this.ENCODED_VALS_WEBSAFE.charAt(n)]=n,this.charToByteMapWebSafe_[this.ENCODED_VALS.charAt(n)]=n)}}};class ha extends Error{constructor(){super(...arguments),this.name="DecodeBase64StringError"}}const rs=function(n){const e=ss(n);return kn.encodeByteArray(e,!0)},Bt=function(n){return rs(n).replace(/\./g,"")},$t=function(n){try{return kn.decodeString(n,!0)}catch(e){console.error("base64Decode failed: ",e)}return null};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function da(n){return os(void 0,n)}function os(n,e){if(!(e instanceof Object))return e;switch(e.constructor){case Date:const t=e;return new Date(t.getTime());case Object:n===void 0&&(n={});break;case Array:n=[];break;default:return e}for(const t in e)!e.hasOwnProperty(t)||!ua(t)||(n[t]=os(n[t],e[t]));return n}function ua(n){return n!=="__proto__"}/**
 * @license
 * Copyright 2022 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function fa(){if(typeof self<"u")return self;if(typeof window<"u")return window;if(typeof global<"u")return global;throw new Error("Unable to locate global object.")}/**
 * @license
 * Copyright 2022 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const pa=()=>fa().__FIREBASE_DEFAULTS__,_a=()=>{if(typeof process>"u"||typeof process.env>"u")return;const n=process.env.__FIREBASE_DEFAULTS__;if(n)return JSON.parse(n)},ga=()=>{if(typeof document>"u")return;let n;try{n=document.cookie.match(/__FIREBASE_DEFAULTS__=([^;]+)/)}catch{return}const e=n&&$t(n[1]);return e&&JSON.parse(e)},as=()=>{try{return la()||pa()||_a()||ga()}catch(n){console.info(`Unable to get __FIREBASE_DEFAULTS__ due to: ${n}`);return}},ma=n=>{var e,t;return(t=(e=as())==null?void 0:e.emulatorHosts)==null?void 0:t[n]},ya=n=>{const e=ma(n);if(!e)return;const t=e.lastIndexOf(":");if(t<=0||t+1===e.length)throw new Error(`Invalid host ${e} with no separate hostname and port!`);const i=parseInt(e.substring(t+1),10);return e[0]==="["?[e.substring(1,t-1),i]:[e.substring(0,t),i]},An=()=>{var n;return(n=as())==null?void 0:n.config};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class j{constructor(){this.reject=()=>{},this.resolve=()=>{},this.promise=new Promise((e,t)=>{this.resolve=e,this.reject=t})}wrapCallback(e){return(t,i)=>{t?this.reject(t):this.resolve(i),typeof e=="function"&&(this.promise.catch(()=>{}),e.length===1?e(t):e(t,i))}}}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function va(n,e){if(n.uid)throw new Error('The "uid" field is no longer supported by mockUserToken. Please use "sub" instead for Firebase Auth User ID.');const t={alg:"none",type:"JWT"},i=e||"demo-project",s=n.iat||0,r=n.sub||n.user_id;if(!r)throw new Error("mockUserToken must contain 'sub' or 'user_id' field!");const o={iss:`https://securetoken.google.com/${i}`,aud:i,iat:s,exp:s+3600,auth_time:s,sub:r,user_id:r,firebase:{sign_in_provider:"custom",identities:{}},...n};return[Bt(JSON.stringify(t)),Bt(JSON.stringify(o)),""].join(".")}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function wa(){return typeof navigator<"u"&&typeof navigator.userAgent=="string"?navigator.userAgent:""}function ls(){return typeof window<"u"&&!!(window.cordova||window.phonegap||window.PhoneGap)&&/ios|iphone|ipod|ipad|android|blackberry|iemobile/i.test(wa())}function ba(){return typeof window<"u"||cs()}function cs(){return typeof WorkerGlobalScope<"u"&&typeof self<"u"&&self instanceof WorkerGlobalScope}function Ca(){return typeof navigator=="object"&&navigator.product==="ReactNative"}function Ea(){return is.NODE_ADMIN===!0}function Sa(){try{return typeof indexedDB=="object"}catch{return!1}}function Ia(){return new Promise((n,e)=>{try{let t=!0;const i="validate-browser-context-for-indexeddb-analytics-module",s=self.indexedDB.open(i);s.onsuccess=()=>{s.result.close(),t||self.indexedDB.deleteDatabase(i),n(!0)},s.onupgradeneeded=()=>{t=!1},s.onerror=()=>{var r;e(((r=s.error)==null?void 0:r.message)||"")}}catch(t){e(t)}})}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const xa="FirebaseError";class je extends Error{constructor(e,t,i){super(t),this.code=e,this.customData=i,this.name=xa,Object.setPrototypeOf(this,je.prototype),Error.captureStackTrace&&Error.captureStackTrace(this,hs.prototype.create)}}class hs{constructor(e,t,i){this.service=e,this.serviceName=t,this.errors=i}create(e,...t){const i=t[0]||{},s=`${this.service}/${e}`,r=this.errors[e],o=r?Ta(r,i):"Error",a=`${this.serviceName}: ${o} (${s}).`;return new je(s,a,i)}}function Ta(n,e){try{let t=0,i="";for(;t<n.length;){const s=n.indexOf("{$",t);if(s===-1){i+=n.substring(t);break}const r=n.indexOf("}",s+2);if(r===-1){i+=n.substring(t);break}const o=n.substring(s+2,r),a=e[o];i+=n.substring(t,s)+(a!=null?String(a):`<${o}?>`),t=r+1}return i}catch{return n}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function lt(n){return JSON.parse(n)}function O(n){return JSON.stringify(n)}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const ds=function(n){let e={},t={},i={},s="";try{const r=n.split(".");e=lt($t(r[0])||""),t=lt($t(r[1])||""),s=r[2],i=t.d||{},delete t.d}catch{}return{header:e,claims:t,data:i,signature:s}},ka=function(n){const e=ds(n),t=e.claims;return!!t&&typeof t=="object"&&t.hasOwnProperty("iat")},Aa=function(n){const e=ds(n).claims;return typeof e=="object"&&e.admin===!0};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function X(n,e){return Object.prototype.hasOwnProperty.call(n,e)}function Ne(n,e){if(Object.prototype.hasOwnProperty.call(n,e))return n[e]}function Nn(n){for(const e in n)if(Object.prototype.hasOwnProperty.call(n,e))return!1;return!0}function Wt(n,e,t){const i={};for(const s in n)Object.prototype.hasOwnProperty.call(n,s)&&(i[s]=e.call(t,n[s],s,n));return i}function Ut(n,e){if(n===e)return!0;const t=Object.keys(n),i=Object.keys(e);for(const s of t){if(!i.includes(s))return!1;const r=n[s],o=e[s];if(us(r)&&us(o)){if(!Ut(r,o))return!1}else if(r!==o)return!1}for(const s of i)if(!t.includes(s))return!1;return!0}function us(n){return n!==null&&typeof n=="object"}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Na(n){const e=[];for(const[t,i]of Object.entries(n))Array.isArray(i)?i.forEach(s=>{e.push(encodeURIComponent(t)+"="+encodeURIComponent(s))}):e.push(encodeURIComponent(t)+"="+encodeURIComponent(i));return e.length?"&"+e.join("&"):""}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Ra{constructor(){this.chain_=[],this.buf_=[],this.W_=[],this.pad_=[],this.inbuf_=0,this.total_=0,this.blockSize=512/8,this.pad_[0]=128;for(let e=1;e<this.blockSize;++e)this.pad_[e]=0;this.reset()}reset(){this.chain_[0]=1732584193,this.chain_[1]=4023233417,this.chain_[2]=2562383102,this.chain_[3]=271733878,this.chain_[4]=3285377520,this.inbuf_=0,this.total_=0}compress_(e,t){t||(t=0);const i=this.W_;if(typeof e=="string")for(let h=0;h<16;h++)i[h]=e.charCodeAt(t)<<24|e.charCodeAt(t+1)<<16|e.charCodeAt(t+2)<<8|e.charCodeAt(t+3),t+=4;else for(let h=0;h<16;h++)i[h]=e[t]<<24|e[t+1]<<16|e[t+2]<<8|e[t+3],t+=4;for(let h=16;h<80;h++){const u=i[h-3]^i[h-8]^i[h-14]^i[h-16];i[h]=(u<<1|u>>>31)&4294967295}let s=this.chain_[0],r=this.chain_[1],o=this.chain_[2],a=this.chain_[3],l=this.chain_[4],c,d;for(let h=0;h<80;h++){h<40?h<20?(c=a^r&(o^a),d=1518500249):(c=r^o^a,d=1859775393):h<60?(c=r&o|a&(r|o),d=2400959708):(c=r^o^a,d=3395469782);const u=(s<<5|s>>>27)+c+l+d+i[h]&4294967295;l=a,a=o,o=(r<<30|r>>>2)&4294967295,r=s,s=u}this.chain_[0]=this.chain_[0]+s&4294967295,this.chain_[1]=this.chain_[1]+r&4294967295,this.chain_[2]=this.chain_[2]+o&4294967295,this.chain_[3]=this.chain_[3]+a&4294967295,this.chain_[4]=this.chain_[4]+l&4294967295}update(e,t){if(e==null)return;t===void 0&&(t=e.length);const i=t-this.blockSize;let s=0;const r=this.buf_;let o=this.inbuf_;for(;s<t;){if(o===0)for(;s<=i;)this.compress_(e,s),s+=this.blockSize;if(typeof e=="string"){for(;s<t;)if(r[o]=e.charCodeAt(s),++o,++s,o===this.blockSize){this.compress_(r),o=0;break}}else for(;s<t;)if(r[o]=e[s],++o,++s,o===this.blockSize){this.compress_(r),o=0;break}}this.inbuf_=o,this.total_+=t}digest(){const e=[];let t=this.total_*8;this.inbuf_<56?this.update(this.pad_,56-this.inbuf_):this.update(this.pad_,this.blockSize-(this.inbuf_-56));for(let s=this.blockSize-1;s>=56;s--)this.buf_[s]=t&255,t/=256;this.compress_(this.buf_);let i=0;for(let s=0;s<5;s++)for(let r=24;r>=0;r-=8)e[i]=this.chain_[s]>>r&255,++i;return e}}function Re(n,e){return`${n} failed: ${e} argument `}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Pa=function(n){const e=[];let t=0;for(let i=0;i<n.length;i++){let s=n.charCodeAt(i);if(s>=55296&&s<=56319){const r=s-55296;i++,f(i<n.length,"Surrogate pair missing trail surrogate.");const o=n.charCodeAt(i)-56320;s=65536+(r<<10)+o}s<128?e[t++]=s:s<2048?(e[t++]=s>>6|192,e[t++]=s&63|128):s<65536?(e[t++]=s>>12|224,e[t++]=s>>6&63|128,e[t++]=s&63|128):(e[t++]=s>>18|240,e[t++]=s>>12&63|128,e[t++]=s>>6&63|128,e[t++]=s&63|128)}return e},Ht=function(n){let e=0;for(let t=0;t<n.length;t++){const i=n.charCodeAt(t);i<128?e++:i<2048?e+=2:i>=55296&&i<=56319?(e+=4,t++):e+=3}return e};/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function U(n){return n&&n._delegate?n._delegate:n}/**
 * @license
 * Copyright 2025 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function fs(n){try{return(n.startsWith("http://")||n.startsWith("https://")?new URL(n).hostname:n).endsWith(".cloudworkstations.dev")}catch{return!1}}async function Da(n){return(await fetch(n,{credentials:"include"})).ok}class Pe{constructor(e,t,i){this.name=e,this.instanceFactory=t,this.type=i,this.multipleInstances=!1,this.serviceProps={},this.instantiationMode="LAZY",this.onInstanceCreated=null}setInstantiationMode(e){return this.instantiationMode=e,this}setMultipleInstances(e){return this.multipleInstances=e,this}setServiceProps(e){return this.serviceProps=e,this}setInstanceCreatedCallback(e){return this.onInstanceCreated=e,this}}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const De="[DEFAULT]";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Rn{constructor(e,t){this.name=e,this.container=t,this.component=null,this.instances=new Map,this.instancesDeferred=new Map,this.instancesOptions=new Map,this.onInitCallbacks=new Map}get(e){const t=this.normalizeInstanceIdentifier(e);if(!this.instancesDeferred.has(t)){const i=new j;if(this.instancesDeferred.set(t,i),this.isInitialized(t)||this.shouldAutoInitialize())try{const s=this.getOrInitializeService({instanceIdentifier:t});s&&i.resolve(s)}catch{}}return this.instancesDeferred.get(t).promise}getImmediate(e){const t=this.normalizeInstanceIdentifier(e==null?void 0:e.identifier),i=(e==null?void 0:e.optional)??!1;if(this.isInitialized(t)||this.shouldAutoInitialize())try{return this.getOrInitializeService({instanceIdentifier:t})}catch(s){if(i)return null;throw s}else{if(i)return null;throw Error(`Service ${this.name} is not available`)}}getComponent(){return this.component}setComponent(e){if(e.name!==this.name)throw Error(`Mismatching Component ${e.name} for Provider ${this.name}.`);if(this.component)throw Error(`Component for ${this.name} has already been provided`);if(this.component=e,!!this.shouldAutoInitialize()){if(Ma(e))try{this.getOrInitializeService({instanceIdentifier:De})}catch{}for(const[t,i]of this.instancesDeferred.entries()){const s=this.normalizeInstanceIdentifier(t);try{const r=this.getOrInitializeService({instanceIdentifier:s});i.resolve(r)}catch{}}}}clearInstance(e=De){this.instancesDeferred.delete(e),this.instancesOptions.delete(e),this.instances.delete(e)}async delete(){const e=Array.from(this.instances.values());await Promise.all([...e.filter(t=>"INTERNAL"in t).map(t=>t.INTERNAL.delete()),...e.filter(t=>"_delete"in t).map(t=>t._delete())])}isComponentSet(){return this.component!=null}isInitialized(e=De){return this.instances.has(e)}getOptions(e=De){return this.instancesOptions.get(e)||{}}initialize(e={}){const{options:t={}}=e,i=this.normalizeInstanceIdentifier(e.instanceIdentifier);if(this.isInitialized(i))throw Error(`${this.name}(${i}) has already been initialized`);if(!this.isComponentSet())throw Error(`Component ${this.name} has not been registered yet`);const s=this.getOrInitializeService({instanceIdentifier:i,options:t});for(const[r,o]of this.instancesDeferred.entries()){const a=this.normalizeInstanceIdentifier(r);i===a&&o.resolve(s)}return s}onInit(e,t){const i=this.normalizeInstanceIdentifier(t),s=this.onInitCallbacks.get(i)??new Set;s.add(e),this.onInitCallbacks.set(i,s);const r=this.instances.get(i);return r&&e(r,i),()=>{s.delete(e)}}invokeOnInitCallbacks(e,t){const i=this.onInitCallbacks.get(t);if(i)for(const s of i)try{s(e,t)}catch{}}getOrInitializeService({instanceIdentifier:e,options:t={}}){let i=this.instances.get(e);if(!i&&this.component&&(i=this.component.instanceFactory(this.container,{instanceIdentifier:Oa(e),options:t}),this.instances.set(e,i),this.instancesOptions.set(e,t),this.invokeOnInitCallbacks(i,e),this.component.onInstanceCreated))try{this.component.onInstanceCreated(this.container,e,i)}catch{}return i||null}normalizeInstanceIdentifier(e=De){return this.component?this.component.multipleInstances?e:De:e}shouldAutoInitialize(){return!!this.component&&this.component.instantiationMode!=="EXPLICIT"}}function Oa(n){return n===De?void 0:n}function Ma(n){return n.instantiationMode==="EAGER"}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Pn{constructor(e){this.name=e,this.providers=new Map}addComponent(e){const t=this.getProvider(e.name);if(t.isComponentSet())throw new Error(`Component ${e.name} has already been registered with ${this.name}`);t.setComponent(e)}addOrOverwriteComponent(e){this.getProvider(e.name).isComponentSet()&&this.providers.delete(e.name),this.addComponent(e)}getProvider(e){if(this.providers.has(e))return this.providers.get(e);const t=new Rn(e,this);return this.providers.set(e,t),t}getProviders(){return Array.from(this.providers.values())}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Dn=[];var x;(function(n){n[n.DEBUG=0]="DEBUG",n[n.VERBOSE=1]="VERBOSE",n[n.INFO=2]="INFO",n[n.WARN=3]="WARN",n[n.ERROR=4]="ERROR",n[n.SILENT=5]="SILENT"})(x||(x={}));const ps={debug:x.DEBUG,verbose:x.VERBOSE,info:x.INFO,warn:x.WARN,error:x.ERROR,silent:x.SILENT},La=x.INFO,Fa={[x.DEBUG]:"log",[x.VERBOSE]:"log",[x.INFO]:"info",[x.WARN]:"warn",[x.ERROR]:"error"},Ba=(n,e,...t)=>{if(e<n.logLevel)return;const i=new Date().toISOString(),s=Fa[e];if(s)console[s](`[${i}]  ${n.name}:`,...t);else throw new Error(`Attempted to log a message with an invalid logType (value: ${e})`)};class _s{constructor(e){this.name=e,this._logLevel=La,this._logHandler=Ba,this._userLogHandler=null,Dn.push(this)}get logLevel(){return this._logLevel}set logLevel(e){if(!(e in x))throw new TypeError(`Invalid value "${e}" assigned to \`logLevel\``);this._logLevel=e}setLogLevel(e){this._logLevel=typeof e=="string"?ps[e]:e}get logHandler(){return this._logHandler}set logHandler(e){if(typeof e!="function")throw new TypeError("Value assigned to `logHandler` must be a function");this._logHandler=e}get userLogHandler(){return this._userLogHandler}set userLogHandler(e){this._userLogHandler=e}debug(...e){this._userLogHandler&&this._userLogHandler(this,x.DEBUG,...e),this._logHandler(this,x.DEBUG,...e)}log(...e){this._userLogHandler&&this._userLogHandler(this,x.VERBOSE,...e),this._logHandler(this,x.VERBOSE,...e)}info(...e){this._userLogHandler&&this._userLogHandler(this,x.INFO,...e),this._logHandler(this,x.INFO,...e)}warn(...e){this._userLogHandler&&this._userLogHandler(this,x.WARN,...e),this._logHandler(this,x.WARN,...e)}error(...e){this._userLogHandler&&this._userLogHandler(this,x.ERROR,...e),this._logHandler(this,x.ERROR,...e)}}function $a(n){Dn.forEach(e=>{e.setLogLevel(n)})}function Wa(n,e){for(const t of Dn){let i=null;e&&e.level&&(i=ps[e.level]),n===null?t.userLogHandler=null:t.userLogHandler=(s,r,...o)=>{const a=o.map(l=>{if(l==null)return null;if(typeof l=="string")return l;if(typeof l=="number"||typeof l=="boolean")return l.toString();if(l instanceof Error)return l.message;try{return JSON.stringify(l)}catch{return null}}).filter(l=>l).join(" ");r>=(i??s.logLevel)&&n({level:x[r].toLowerCase(),message:a,args:o,type:s.name})}}}const Ua=(n,e)=>e.some(t=>n instanceof t);let gs,ms;function Ha(){return gs||(gs=[IDBDatabase,IDBObjectStore,IDBIndex,IDBCursor,IDBTransaction])}function za(){return ms||(ms=[IDBCursor.prototype.advance,IDBCursor.prototype.continue,IDBCursor.prototype.continuePrimaryKey])}const ys=new WeakMap,On=new WeakMap,vs=new WeakMap,Mn=new WeakMap,Ln=new WeakMap;function Va(n){const e=new Promise((t,i)=>{const s=()=>{n.removeEventListener("success",r),n.removeEventListener("error",o)},r=()=>{t(_e(n.result)),s()},o=()=>{i(n.error),s()};n.addEventListener("success",r),n.addEventListener("error",o)});return e.then(t=>{t instanceof IDBCursor&&ys.set(t,n)}).catch(()=>{}),Ln.set(e,n),e}function ja(n){if(On.has(n))return;const e=new Promise((t,i)=>{const s=()=>{n.removeEventListener("complete",r),n.removeEventListener("error",o),n.removeEventListener("abort",o)},r=()=>{t(),s()},o=()=>{i(n.error||new DOMException("AbortError","AbortError")),s()};n.addEventListener("complete",r),n.addEventListener("error",o),n.addEventListener("abort",o)});On.set(n,e)}let Fn={get(n,e,t){if(n instanceof IDBTransaction){if(e==="done")return On.get(n);if(e==="objectStoreNames")return n.objectStoreNames||vs.get(n);if(e==="store")return t.objectStoreNames[1]?void 0:t.objectStore(t.objectStoreNames[0])}return _e(n[e])},set(n,e,t){return n[e]=t,!0},has(n,e){return n instanceof IDBTransaction&&(e==="done"||e==="store")?!0:e in n}};function Ga(n){Fn=n(Fn)}function qa(n){return n===IDBDatabase.prototype.transaction&&!("objectStoreNames"in IDBTransaction.prototype)?function(e,...t){const i=n.call(Bn(this),e,...t);return vs.set(i,e.sort?e.sort():[e]),_e(i)}:za().includes(n)?function(...e){return n.apply(Bn(this),e),_e(ys.get(this))}:function(...e){return _e(n.apply(Bn(this),e))}}function Ka(n){return typeof n=="function"?qa(n):(n instanceof IDBTransaction&&ja(n),Ua(n,Ha())?new Proxy(n,Fn):n)}function _e(n){if(n instanceof IDBRequest)return Va(n);if(Mn.has(n))return Mn.get(n);const e=Ka(n);return e!==n&&(Mn.set(n,e),Ln.set(e,n)),e}const Bn=n=>Ln.get(n);function Qa(n,e,{blocked:t,upgrade:i,blocking:s,terminated:r}={}){const o=indexedDB.open(n,e),a=_e(o);return i&&o.addEventListener("upgradeneeded",l=>{i(_e(o.result),l.oldVersion,l.newVersion,_e(o.transaction),l)}),t&&o.addEventListener("blocked",l=>t(l.oldVersion,l.newVersion,l)),a.then(l=>{r&&l.addEventListener("close",()=>r()),s&&l.addEventListener("versionchange",c=>s(c.oldVersion,c.newVersion,c))}).catch(()=>{}),a}const Ya=["get","getKey","getAll","getAllKeys","count"],Ja=["put","add","delete","clear"],$n=new Map;function ws(n,e){if(!(n instanceof IDBDatabase&&!(e in n)&&typeof e=="string"))return;if($n.get(e))return $n.get(e);const t=e.replace(/FromIndex$/,""),i=e!==t,s=Ja.includes(t);if(!(t in(i?IDBIndex:IDBObjectStore).prototype)||!(s||Ya.includes(t)))return;const r=async function(o,...a){const l=this.transaction(o,s?"readwrite":"readonly");let c=l.store;return i&&(c=c.index(a.shift())),(await Promise.all([c[t](...a),s&&l.done]))[0]};return $n.set(e,r),r}Ga(n=>({...n,get:(e,t,i)=>ws(e,t)||n.get(e,t,i),has:(e,t)=>!!ws(e,t)||n.has(e,t)}));/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Xa{constructor(e){this.container=e}getPlatformInfoString(){return this.container.getProviders().map(t=>{if(Za(t)){const i=t.getImmediate();return`${i.library}/${i.version}`}else return null}).filter(t=>t).join(" ")}}function Za(n){const e=n.getComponent();return(e==null?void 0:e.type)==="VERSION"}const zt="@firebase/app",Wn="0.16.2";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const he=new _s("@firebase/app"),el="@firebase/app-compat",tl="@firebase/analytics-compat",nl="@firebase/analytics",il="@firebase/app-check-compat",sl="@firebase/app-check",rl="@firebase/auth",ol="@firebase/auth-compat",al="@firebase/database",ll="@firebase/data-connect",cl="@firebase/database-compat",hl="@firebase/functions",dl="@firebase/functions-compat",ul="@firebase/installations",fl="@firebase/installations-compat",pl="@firebase/messaging",_l="@firebase/messaging-compat",gl="@firebase/performance",ml="@firebase/performance-compat",yl="@firebase/remote-config",vl="@firebase/remote-config-compat",wl="@firebase/storage",bl="@firebase/storage-compat",Cl="@firebase/firestore",El="@firebase/ai",Sl="@firebase/firestore-compat",Il="firebase",xl="12.19.0";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const ct="[DEFAULT]",Tl={[zt]:"fire-core",[el]:"fire-core-compat",[nl]:"fire-analytics",[tl]:"fire-analytics-compat",[sl]:"fire-app-check",[il]:"fire-app-check-compat",[rl]:"fire-auth",[ol]:"fire-auth-compat",[al]:"fire-rtdb",[ll]:"fire-data-connect",[cl]:"fire-rtdb-compat",[hl]:"fire-fn",[dl]:"fire-fn-compat",[ul]:"fire-iid",[fl]:"fire-iid-compat",[pl]:"fire-fcm",[_l]:"fire-fcm-compat",[gl]:"fire-perf",[ml]:"fire-perf-compat",[yl]:"fire-rc",[vl]:"fire-rc-compat",[wl]:"fire-gcs",[bl]:"fire-gcs-compat",[Cl]:"fire-fst",[Sl]:"fire-fst-compat",[El]:"fire-vertex","fire-js":"fire-js",[Il]:"fire-js-all"};/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const ge=new Map,Ge=new Map,qe=new Map;function Un(n,e){try{n.container.addComponent(e)}catch(t){he.debug(`Component ${e.name} failed to register with FirebaseApp ${n.name}`,t)}}function kl(n,e){n.container.addOrOverwriteComponent(e)}function ht(n){const e=n.name;if(qe.has(e))return he.debug(`There were multiple attempts to register component ${e}.`),!1;qe.set(e,n);for(const t of ge.values())Un(t,n);for(const t of Ge.values())Un(t,n);return!0}function Hn(n,e){const t=n.container.getProvider("heartbeat").getImmediate({optional:!0});return t&&t.triggerHeartbeat(),n.container.getProvider(e)}function Al(n,e,t=ct){Hn(n,e).clearInstance(t)}function zn(n){return n.options!==void 0}function bs(n){return zn(n)?!1:"authIdToken"in n||"appCheckToken"in n||"releaseOnDeref"in n||"automaticDataCollectionEnabled"in n}function Cs(n){return n==null?!1:n.settings!==void 0}function Nl(){qe.clear()}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Rl={"no-app":"No Firebase App '{$appName}' has been created - call initializeApp() first","bad-app-name":"Illegal App name: '{$appName}'","duplicate-app":"Firebase App named '{$appName}' already exists with different {$mismatchedParam}. Existing: '{$oldValue}'. New: '{$newValue}'.","app-deleted":"Firebase App named '{$appName}' already deleted","server-app-deleted":"Firebase Server App has been deleted","no-options":"Need to provide options, when not being deployed to hosting via source.","invalid-app-argument":"firebase.{$appName}() takes either no argument or a Firebase App instance.","invalid-log-argument":"First argument to `onLog` must be null or a function.","idb-open":"Error thrown when opening IndexedDB. Original error: {$originalErrorMessage}.","idb-get":"Error thrown when reading from IndexedDB. Original error: {$originalErrorMessage}.","idb-set":"Error thrown when writing to IndexedDB. Original error: {$originalErrorMessage}.","idb-delete":"Error thrown when deleting from IndexedDB. Original error: {$originalErrorMessage}.","finalization-registry-not-supported":"FirebaseServerApp deleteOnDeref field defined but the JS runtime does not support FinalizationRegistry.","invalid-server-app-environment":"FirebaseServerApp is not for use in browser environments."},H=new hs("app","Firebase",Rl);/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Es{constructor(e,t,i){this._isDeleted=!1,this._options={...e},this._config={...t},this._name=t.name,this._automaticDataCollectionEnabled=t.automaticDataCollectionEnabled,this._container=i,this.container.addComponent(new Pe("app",()=>this,"PUBLIC"))}get automaticDataCollectionEnabled(){return this.checkDestroyed(),this._automaticDataCollectionEnabled}set automaticDataCollectionEnabled(e){this.checkDestroyed(),this._automaticDataCollectionEnabled=e}get name(){return this.checkDestroyed(),this._name}get options(){return this.checkDestroyed(),this._options}get config(){return this.checkDestroyed(),this._config}get container(){return this._container}get isDeleted(){return this._isDeleted}set isDeleted(e){this._isDeleted=e}checkDestroyed(){if(this.isDeleted)throw H.create("app-deleted",{appName:this._name})}}/**
 * @license
 * Copyright 2023 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Ss(n,e){const t=$t(n.split(".")[1]);if(t===null){console.error(`FirebaseServerApp ${e} is invalid: second part could not be parsed.`);return}if(JSON.parse(t).exp===void 0){console.error(`FirebaseServerApp ${e} is invalid: expiration claim could not be parsed`);return}const s=JSON.parse(t).exp*1e3,r=new Date().getTime();s-r<=0&&console.error(`FirebaseServerApp ${e} is invalid: the token has expired.`)}class Pl extends Es{constructor(e,t,i,s){const r=t.automaticDataCollectionEnabled!==void 0?t.automaticDataCollectionEnabled:!0,o={name:i,automaticDataCollectionEnabled:r};if(e.apiKey!==void 0)super(e,o,s);else{const a=e;super(a.options,o,s)}this._serverConfig={automaticDataCollectionEnabled:r,...t},this._serverConfig.authIdToken&&Ss(this._serverConfig.authIdToken,"authIdToken"),this._serverConfig.appCheckToken&&Ss(this._serverConfig.appCheckToken,"appCheckToken"),this._finalizationRegistry=null,typeof FinalizationRegistry<"u"&&(this._finalizationRegistry=new FinalizationRegistry(()=>{this.automaticCleanup()})),this._refCount=0,this.incRefCount(this._serverConfig.releaseOnDeref),this._serverConfig.releaseOnDeref=void 0,t.releaseOnDeref=void 0,me(zt,Wn,"serverapp")}toJSON(){}get refCount(){return this._refCount}incRefCount(e){this.isDeleted||(this._refCount++,e!==void 0&&this._finalizationRegistry!==null&&this._finalizationRegistry.register(e,this))}decRefCount(){return this.isDeleted?0:--this._refCount}automaticCleanup(){ks(this)}get settings(){return this.checkDestroyed(),this._serverConfig}checkDestroyed(){if(this.isDeleted)throw H.create("server-app-deleted")}}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Is=xl;function xs(n,e={}){let t=n;typeof e!="object"&&(e={name:e});const i={name:ct,automaticDataCollectionEnabled:!0,...e},s=i.name;if(typeof s!="string"||!s)throw H.create("bad-app-name",{appName:String(s)});if(t||(t=An()),!t)throw H.create("no-options");const r=ge.get(s);if(r)if(Ut(t,r.options)){if(Ut(i,r.config))return r;throw H.create("duplicate-app",{appName:s,mismatchedParam:"config",oldValue:JSON.stringify(r.config),newValue:JSON.stringify(i)})}else throw H.create("duplicate-app",{appName:s,mismatchedParam:"options",oldValue:JSON.stringify(r.options),newValue:JSON.stringify(t)});const o=new Pn(s);for(const l of qe.values())o.addComponent(l);const a=new Es(t,i,o);return ge.set(s,a),a}function Dl(n,e={}){if(ba()&&!cs())throw H.create("invalid-server-app-environment");let t,i=e||{};if(n&&(zn(n)?t=n.options:bs(n)?i=n:t=n),i.automaticDataCollectionEnabled===void 0&&(i.automaticDataCollectionEnabled=!0),t||(t=An()),!t)throw H.create("no-options");const s={...i,...t};s.releaseOnDeref!==void 0&&delete s.releaseOnDeref;const r=d=>[...d].reduce((h,u)=>Math.imul(31,h)+u.charCodeAt(0)|0,0);if(i.releaseOnDeref!==void 0&&typeof FinalizationRegistry>"u")throw H.create("finalization-registry-not-supported",{});const o=""+r(JSON.stringify(s)),a=Ge.get(o);if(a)return a.incRefCount(i.releaseOnDeref),a;const l=new Pn(o);for(const d of qe.values())l.addComponent(d);const c=new Pl(t,i,o,l);return Ge.set(o,c),c}function Ts(n=ct){const e=ge.get(n);if(!e&&n===ct&&An())return xs();if(!e)throw H.create("no-app",{appName:n});return e}function Ol(){return Array.from(ge.values())}async function ks(n){let e=!1;const t=n.name;ge.has(t)?(e=!0,ge.delete(t)):Ge.has(t)&&n.decRefCount()<=0&&(Ge.delete(t),e=!0),e&&(await Promise.all(n.container.getProviders().map(i=>i.delete())),n.isDeleted=!0)}function me(n,e,t){let i=Tl[n]??n;t&&(i+=`-${t}`);const s=i.match(/\s|\//),r=e.match(/\s|\//);if(s||r){const o=[`Unable to register library "${i}" with version "${e}":`];s&&o.push(`library name "${i}" contains illegal characters (whitespace or "/")`),s&&r&&o.push("and"),r&&o.push(`version name "${e}" contains illegal characters (whitespace or "/")`),he.warn(o.join(" "));return}ht(new Pe(`${i}-version`,()=>({library:i,version:e}),"VERSION"))}function Ml(n,e){if(n!==null&&typeof n!="function")throw H.create("invalid-log-argument");Wa(n,e)}function Ll(n){$a(n)}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Fl="firebase-heartbeat-database",Bl=1,dt="firebase-heartbeat-store";let Vn=null;function As(){return Vn||(Vn=Qa(Fl,Bl,{upgrade:(n,e)=>{switch(e){case 0:try{n.createObjectStore(dt)}catch(t){console.warn(t)}}}}).catch(n=>{throw H.create("idb-open",{originalErrorMessage:n.message})})),Vn}async function $l(n){try{const t=(await As()).transaction(dt),i=await t.objectStore(dt).get(Rs(n));return await t.done,i}catch(e){if(e instanceof je)he.warn(e.message);else{const t=H.create("idb-get",{originalErrorMessage:e==null?void 0:e.message});he.warn(t.message)}}}async function Ns(n,e){try{const i=(await As()).transaction(dt,"readwrite");await i.objectStore(dt).put(e,Rs(n)),await i.done}catch(t){if(t instanceof je)he.warn(t.message);else{const i=H.create("idb-set",{originalErrorMessage:t==null?void 0:t.message});he.warn(i.message)}}}function Rs(n){return`${n.name}!${n.options.appId}`}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Wl=1024,Ul=30;class Hl{constructor(e){this.container=e,this._heartbeatsCache=null;const t=this.container.getProvider("app").getImmediate();this._storage=new Vl(t),this._heartbeatsCachePromise=this._storage.read().then(i=>(this._heartbeatsCache=i,i))}async triggerHeartbeat(){var e,t;try{const s=this.container.getProvider("platform-logger").getImmediate().getPlatformInfoString(),r=Ps();if(((e=this._heartbeatsCache)==null?void 0:e.heartbeats)==null&&(this._heartbeatsCache=await this._heartbeatsCachePromise,((t=this._heartbeatsCache)==null?void 0:t.heartbeats)==null)||this._heartbeatsCache.lastSentHeartbeatDate===r||this._heartbeatsCache.heartbeats.some(o=>o.date===r))return;if(this._heartbeatsCache.heartbeats.push({date:r,agent:s}),this._heartbeatsCache.heartbeats.length>Ul){const o=jl(this._heartbeatsCache.heartbeats);this._heartbeatsCache.heartbeats.splice(o,1)}return this._storage.overwrite(this._heartbeatsCache)}catch(i){he.warn(i)}}async getHeartbeatsHeader(){var e;try{if(this._heartbeatsCache===null&&await this._heartbeatsCachePromise,((e=this._heartbeatsCache)==null?void 0:e.heartbeats)==null||this._heartbeatsCache.heartbeats.length===0)return"";const t=Ps(),{heartbeatsToSend:i,unsentEntries:s}=zl(this._heartbeatsCache.heartbeats),r=Bt(JSON.stringify({version:2,heartbeats:i}));return this._heartbeatsCache.lastSentHeartbeatDate=t,s.length>0?(this._heartbeatsCache.heartbeats=s,await this._storage.overwrite(this._heartbeatsCache)):(this._heartbeatsCache.heartbeats=[],this._storage.overwrite(this._heartbeatsCache)),r}catch(t){return he.warn(t),""}}}function Ps(){return new Date().toISOString().substring(0,10)}function zl(n,e=Wl){const t=[];let i=n.slice();for(const s of n){const r=t.find(o=>o.agent===s.agent);if(r){if(r.dates.push(s.date),Ds(t)>e){r.dates.pop();break}}else if(t.push({agent:s.agent,dates:[s.date]}),Ds(t)>e){t.pop();break}i=i.slice(1)}return{heartbeatsToSend:t,unsentEntries:i}}class Vl{constructor(e){this.app=e,this._canUseIndexedDBPromise=this.runIndexedDBEnvironmentCheck()}async runIndexedDBEnvironmentCheck(){return Sa()?Ia().then(()=>!0).catch(()=>!1):!1}async read(){if(await this._canUseIndexedDBPromise){const t=await $l(this.app);return t!=null&&t.heartbeats?t:{heartbeats:[]}}else return{heartbeats:[]}}async overwrite(e){if(await this._canUseIndexedDBPromise){const i=await this.read();return Ns(this.app,{lastSentHeartbeatDate:e.lastSentHeartbeatDate??i.lastSentHeartbeatDate,heartbeats:e.heartbeats})}else return}async add(e){if(await this._canUseIndexedDBPromise){const i=await this.read();return Ns(this.app,{lastSentHeartbeatDate:e.lastSentHeartbeatDate??i.lastSentHeartbeatDate,heartbeats:[...i.heartbeats,...e.heartbeats]})}else return}}function Ds(n){return Bt(JSON.stringify({version:2,heartbeats:n})).length}function jl(n){if(n.length===0)return-1;let e=0,t=n[0].date;for(let i=1;i<n.length;i++)n[i].date<t&&(t=n[i].date,e=i);return e}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Gl(n){ht(new Pe("platform-logger",e=>new Xa(e),"PRIVATE")),ht(new Pe("heartbeat",e=>new Hl(e),"PRIVATE")),me(zt,Wn,n),me(zt,Wn,"esm2020"),me("fire-js","")}/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */Gl("");var ql="firebase",Kl="12.19.0";/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */me(ql,Kl,"app");const Ql=Object.freeze(Object.defineProperty({__proto__:null,FirebaseError:je,SDK_VERSION:Is,_DEFAULT_ENTRY_NAME:ct,_addComponent:Un,_addOrOverwriteComponent:kl,_apps:ge,_clearComponents:Nl,_components:qe,_getProvider:Hn,_isFirebaseApp:zn,_isFirebaseServerApp:Cs,_isFirebaseServerAppSettings:bs,_registerComponent:ht,_removeServiceInstance:Al,_serverApps:Ge,deleteApp:ks,getApp:Ts,getApps:Ol,initializeApp:xs,initializeServerApp:Dl,onLog:Ml,registerVersion:me,setLogLevel:Ll},Symbol.toStringTag,{value:"Module"})),Os="@firebase/database",Ms="1.1.5";/**
 * @license
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */let Ls="";function jn(n){Ls=n}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Yl{constructor(e){this.domStorage_=e,this.prefix_="firebase:"}set(e,t){t==null?this.domStorage_.removeItem(this.prefixedName_(e)):this.domStorage_.setItem(this.prefixedName_(e),O(t))}get(e){const t=this.domStorage_.getItem(this.prefixedName_(e));return t==null?null:lt(t)}remove(e){this.domStorage_.removeItem(this.prefixedName_(e))}prefixedName_(e){return this.prefix_+e}toString(){return this.domStorage_.toString()}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Jl{constructor(){this.cache_={},this.isInMemoryStorage=!0}set(e,t){t==null?delete this.cache_[e]:this.cache_[e]=t}get(e){return X(this.cache_,e)?this.cache_[e]:null}remove(e){delete this.cache_[e]}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Fs=function(n){try{if(typeof window<"u"&&typeof window[n]<"u"){const e=window[n];return e.setItem("firebase:sentinel","cache"),e.removeItem("firebase:sentinel"),new Yl(e)}}catch{}return new Jl},Oe=Fs("localStorage"),Gn=Fs("sessionStorage");/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ke=new _s("@firebase/database"),Bs=(function(){let n=1;return function(){return n++}})(),$s=function(n){const e=Pa(n),t=new Ra;t.update(e);const i=t.digest();return kn.encodeByteArray(i)},ut=function(...n){let e="";for(let t=0;t<n.length;t++){const i=n[t];Array.isArray(i)||i&&typeof i=="object"&&typeof i.length=="number"?e+=ut.apply(null,i):typeof i=="object"?e+=O(i):e+=i,e+=" "}return e};let Me=null,Ws=!0;const Us=function(n,e){f(!e||n===!0||n===!1,"Can't turn on custom loggers persistently."),n===!0?(Ke.logLevel=x.VERBOSE,Me=Ke.log.bind(Ke),e&&Gn.set("logging_enabled",!0)):typeof n=="function"?Me=n:(Me=null,Gn.remove("logging_enabled"))},M=function(...n){if(Ws===!0&&(Ws=!1,Me===null&&Gn.get("logging_enabled")===!0&&Us(!0)),Me){const e=ut.apply(null,n);Me(e)}},ft=function(n){return function(...e){M(n,...e)}},qn=function(...n){const e="FIREBASE INTERNAL ERROR: "+ut(...n);Ke.error(e)},se=function(...n){const e=`FIREBASE FATAL ERROR: ${ut(...n)}`;throw Ke.error(e),new Error(e)},$=function(...n){const e="FIREBASE WARNING: "+ut(...n);Ke.warn(e)},Xl=function(){typeof window<"u"&&window.location&&window.location.protocol&&window.location.protocol.indexOf("https:")!==-1&&$("Insecure Firebase access from a secure page. Please use https in calls to new Firebase().")},Vt=function(n){return typeof n=="number"&&(n!==n||n===Number.POSITIVE_INFINITY||n===Number.NEGATIVE_INFINITY)},Zl=function(n){if(document.readyState==="complete")n();else{let e=!1;const t=function(){if(!document.body){setTimeout(t,Math.floor(10));return}e||(e=!0,n())};document.addEventListener?(document.addEventListener("DOMContentLoaded",t,!1),window.addEventListener("load",t,!1)):document.attachEvent&&(document.attachEvent("onreadystatechange",()=>{document.readyState==="complete"&&t()}),window.attachEvent("onload",t))}},ye="[MIN_NAME]",de="[MAX_NAME]",Le=function(n,e){if(n===e)return 0;if(n===ye||e===de)return-1;if(e===ye||n===de)return 1;{const t=Vs(n),i=Vs(e);return t!==null?i!==null?t-i===0?n.length-e.length:t-i:-1:i!==null?1:n<e?-1:1}},ec=function(n,e){return n===e?0:n<e?-1:1},pt=function(n,e){if(e&&n in e)return e[n];throw new Error("Missing required key ("+n+") in object: "+O(e))},Kn=function(n){if(typeof n!="object"||n===null)return O(n);const e=[];for(const i in n)e.push(i);e.sort();let t="{";for(let i=0;i<e.length;i++)i!==0&&(t+=","),t+=O(e[i]),t+=":",t+=Kn(n[e[i]]);return t+="}",t},Hs=function(n,e){const t=n.length;if(t<=e)return[n];const i=[];for(let s=0;s<t;s+=e)s+e>t?i.push(n.substring(s,t)):i.push(n.substring(s,s+e));return i};function L(n,e){for(const t in n)n.hasOwnProperty(t)&&e(t,n[t])}const zs=function(n){f(!Vt(n),"Invalid JSON number");const e=11,t=52,i=(1<<e-1)-1;let s,r,o,a,l;n===0?(r=0,o=0,s=1/n===-1/0?1:0):(s=n<0,n=Math.abs(n),n>=Math.pow(2,1-i)?(a=Math.min(Math.floor(Math.log(n)/Math.LN2),i),r=a+i,o=Math.round(n*Math.pow(2,t-a)-Math.pow(2,t))):(r=0,o=Math.round(n/Math.pow(2,1-i-t))));const c=[];for(l=t;l;l-=1)c.push(o%2?1:0),o=Math.floor(o/2);for(l=e;l;l-=1)c.push(r%2?1:0),r=Math.floor(r/2);c.push(s?1:0),c.reverse();const d=c.join("");let h="";for(l=0;l<64;l+=8){let u=parseInt(d.substr(l,8),2).toString(16);u.length===1&&(u="0"+u),h=h+u}return h.toLowerCase()},tc=function(){return!!(typeof window=="object"&&window.chrome&&window.chrome.extension&&!/^chrome/.test(window.location.href))},nc=function(){return typeof Windows=="object"&&typeof Windows.UI=="object"};function ic(n,e){let t="Unknown Error";n==="too_big"?t="The data requested exceeds the maximum size that can be accessed with a single request.":n==="permission_denied"?t="Client doesn't have permission to access the desired data.":n==="unavailable"&&(t="The service is unavailable");const i=new Error(n+" at "+e._path.toString()+": "+t);return i.code=n.toUpperCase(),i}const sc=new RegExp("^-?(0*)\\d{1,10}$"),rc=-2147483648,oc=2147483647,Vs=function(n){if(sc.test(n)){const e=Number(n);if(e>=rc&&e<=oc)return e}return null},Qe=function(n){try{n()}catch(e){setTimeout(()=>{const t=e.stack||"";throw $("Exception was thrown by user callback.",t),e},Math.floor(0))}},ac=function(){return(typeof window=="object"&&window.navigator&&window.navigator.userAgent||"").search(/googlebot|google webmaster tools|bingbot|yahoo! slurp|baiduspider|yandexbot|duckduckbot/i)>=0},_t=function(n,e){const t=setTimeout(n,e);return typeof t=="number"&&typeof Deno<"u"&&Deno.unrefTimer?Deno.unrefTimer(t):typeof t=="object"&&t.unref&&t.unref(),t};/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class lc{constructor(e,t){this.appCheckProvider=t,this.appName=e.name,Cs(e)&&e.settings.appCheckToken&&(this.serverAppAppCheckToken=e.settings.appCheckToken),this.appCheck=t==null?void 0:t.getImmediate({optional:!0}),this.appCheck||t==null||t.get().then(i=>this.appCheck=i)}getToken(e){if(this.serverAppAppCheckToken){if(e)throw new Error("Attempted reuse of `FirebaseServerApp.appCheckToken` after previous usage failed.");return Promise.resolve({token:this.serverAppAppCheckToken})}return this.appCheck?this.appCheck.getToken(e):new Promise((t,i)=>{setTimeout(()=>{this.appCheck?this.getToken(e).then(t,i):t(null)},0)})}addTokenChangeListener(e){var t;(t=this.appCheckProvider)==null||t.get().then(i=>i.addTokenListener(e))}notifyForInvalidToken(){$(`Provided AppCheck credentials for the app named "${this.appName}" are invalid. This usually indicates your app was not initialized correctly.`)}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class cc{constructor(e,t,i){this.appName_=e,this.firebaseOptions_=t,this.authProvider_=i,this.auth_=null,this.auth_=i.getImmediate({optional:!0}),this.auth_||i.onInit(s=>this.auth_=s)}getToken(e){return this.auth_?this.auth_.getToken(e).catch(t=>t&&t.code==="auth/token-not-initialized"?(M("Got auth/token-not-initialized error.  Treating as null token."),null):Promise.reject(t)):new Promise((t,i)=>{setTimeout(()=>{this.auth_?this.getToken(e).then(t,i):t(null)},0)})}addTokenChangeListener(e){this.auth_?this.auth_.addAuthTokenListener(e):this.authProvider_.get().then(t=>t.addAuthTokenListener(e))}removeTokenChangeListener(e){this.authProvider_.get().then(t=>t.removeAuthTokenListener(e))}notifyForInvalidToken(){let e='Provided authentication credentials for the app named "'+this.appName_+'" are invalid. This usually indicates your app was not initialized correctly. ';"credential"in this.firebaseOptions_?e+='Make sure the "credential" property provided to initializeApp() is authorized to access the specified "databaseURL" and is from the correct project.':"serviceAccount"in this.firebaseOptions_?e+='Make sure the "serviceAccount" property provided to initializeApp() is authorized to access the specified "databaseURL" and is from the correct project.':e+='Make sure the "apiKey" and "databaseURL" properties provided to initializeApp() match the values provided for your app at https://console.firebase.google.com/.',$(e)}}class Ye{constructor(e){this.accessToken=e}getToken(e){return Promise.resolve({accessToken:this.accessToken})}addTokenChangeListener(e){e(this.accessToken)}removeTokenChangeListener(e){}notifyForInvalidToken(){}}Ye.OWNER="owner";/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Qn="5",js="v",Gs="s",qs="r",Ks="f",Qs=/(console\.firebase|firebase-console-\w+\.corp|firebase\.corp)\.google\.com/,Ys="ls",Js="p",Yn="ac",Xs="websocket",Zs="long_polling";/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class er{constructor(e,t,i,s,r=!1,o="",a=!1,l=!1,c=null){this.secure=t,this.namespace=i,this.webSocketOnly=s,this.nodeAdmin=r,this.persistenceKey=o,this.includeNamespaceInQueryParams=a,this.isUsingEmulator=l,this.emulatorOptions=c,this._host=e.toLowerCase(),this._domain=this._host.substr(this._host.indexOf(".")+1),this.internalHost=Oe.get("host:"+e)||this._host}isCacheableHost(){return this.internalHost.substr(0,2)==="s-"}isCustomHost(){return this._domain!=="firebaseio.com"&&this._domain!=="firebaseio-demo.com"}get host(){return this._host}set host(e){e!==this.internalHost&&(this.internalHost=e,this.isCacheableHost()&&Oe.set("host:"+this._host,this.internalHost))}toString(){let e=this.toURLString();return this.persistenceKey&&(e+="<"+this.persistenceKey+">"),e}toURLString(){const e=this.secure?"https://":"http://",t=this.includeNamespaceInQueryParams?`?ns=${this.namespace}`:"";return`${e}${this.host}/${t}`}}function hc(n){return n.host!==n.internalHost||n.isCustomHost()||n.includeNamespaceInQueryParams}function tr(n,e,t){f(typeof e=="string","typeof type must == string"),f(typeof t=="object","typeof params must == object");let i;if(e===Xs)i=(n.secure?"wss://":"ws://")+n.internalHost+"/.ws?";else if(e===Zs)i=(n.secure?"https://":"http://")+n.internalHost+"/.lp?";else throw new Error("Unknown connection type: "+e);hc(n)&&(t.ns=n.namespace);const s=[];return L(t,(r,o)=>{s.push(r+"="+o)}),i+s.join("&")}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class dc{constructor(){this.counters_={}}incrementCounter(e,t=1){X(this.counters_,e)||(this.counters_[e]=0),this.counters_[e]+=t}get(){return da(this.counters_)}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Jn={},Xn={};function Zn(n){const e=n.toString();return Jn[e]||(Jn[e]=new dc),Jn[e]}function uc(n,e){const t=n.toString();return Xn[t]||(Xn[t]=e()),Xn[t]}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class fc{constructor(e){this.onMessage_=e,this.pendingResponses=[],this.currentResponseNum=0,this.closeAfterResponse=-1,this.onClose=null}closeAfter(e,t){this.closeAfterResponse=e,this.onClose=t,this.closeAfterResponse<this.currentResponseNum&&(this.onClose(),this.onClose=null)}handleResponse(e,t){for(this.pendingResponses[e]=t;this.pendingResponses[this.currentResponseNum];){const i=this.pendingResponses[this.currentResponseNum];delete this.pendingResponses[this.currentResponseNum];for(let s=0;s<i.length;++s)i[s]&&Qe(()=>{this.onMessage_(i[s])});if(this.currentResponseNum===this.closeAfterResponse){this.onClose&&(this.onClose(),this.onClose=null);break}this.currentResponseNum++}}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const nr="start",pc="close",_c="pLPCommand",gc="pRTLPCB",ir="id",sr="pw",rr="ser",mc="cb",yc="seg",vc="ts",wc="d",bc="dframe",or=1870,ar=30,Cc=or-ar,Ec=25e3,Sc=3e4;class ve{constructor(e,t,i,s,r,o,a){this.connId=e,this.repoInfo=t,this.applicationId=i,this.appCheckToken=s,this.authToken=r,this.transportSessionId=o,this.lastSessionId=a,this.bytesSent=0,this.bytesReceived=0,this.everConnected_=!1,this.log_=ft(e),this.stats_=Zn(t),this.urlFn=l=>(this.appCheckToken&&(l[Yn]=this.appCheckToken),tr(t,Zs,l))}open(e,t){this.curSegmentNum=0,this.onDisconnect_=t,this.myPacketOrderer=new fc(e),this.isClosed_=!1,this.connectTimeoutTimer_=setTimeout(()=>{this.log_("Timed out trying to connect."),this.onClosed_(),this.connectTimeoutTimer_=null},Math.floor(Sc)),Zl(()=>{if(this.isClosed_)return;this.scriptTagHolder=new ei((...r)=>{const[o,a,l,c,d]=r;if(this.incrementIncomingBytes_(r),!!this.scriptTagHolder)if(this.connectTimeoutTimer_&&(clearTimeout(this.connectTimeoutTimer_),this.connectTimeoutTimer_=null),this.everConnected_=!0,o===nr)this.id=a,this.password=l;else if(o===pc)a?(this.scriptTagHolder.sendNewPolls=!1,this.myPacketOrderer.closeAfter(a,()=>{this.onClosed_()})):this.onClosed_();else throw new Error("Unrecognized command received: "+o)},(...r)=>{const[o,a]=r;this.incrementIncomingBytes_(r),this.myPacketOrderer.handleResponse(o,a)},()=>{this.onClosed_()},this.urlFn);const i={};i[nr]="t",i[rr]=Math.floor(Math.random()*1e8),this.scriptTagHolder.uniqueCallbackIdentifier&&(i[mc]=this.scriptTagHolder.uniqueCallbackIdentifier),i[js]=Qn,this.transportSessionId&&(i[Gs]=this.transportSessionId),this.lastSessionId&&(i[Ys]=this.lastSessionId),this.applicationId&&(i[Js]=this.applicationId),this.appCheckToken&&(i[Yn]=this.appCheckToken),typeof location<"u"&&location.hostname&&Qs.test(location.hostname)&&(i[qs]=Ks);const s=this.urlFn(i);this.log_("Connecting via long-poll to "+s),this.scriptTagHolder.addTag(s,()=>{})})}start(){this.scriptTagHolder.startLongPoll(this.id,this.password),this.addDisconnectPingFrame(this.id,this.password)}static forceAllow(){ve.forceAllow_=!0}static forceDisallow(){ve.forceDisallow_=!0}static isAvailable(){return ve.forceAllow_?!0:!ve.forceDisallow_&&typeof document<"u"&&document.createElement!=null&&!tc()&&!nc()}markConnectionHealthy(){}shutdown_(){this.isClosed_=!0,this.scriptTagHolder&&(this.scriptTagHolder.close(),this.scriptTagHolder=null),this.myDisconnFrame&&(document.body.removeChild(this.myDisconnFrame),this.myDisconnFrame=null),this.connectTimeoutTimer_&&(clearTimeout(this.connectTimeoutTimer_),this.connectTimeoutTimer_=null)}onClosed_(){this.isClosed_||(this.log_("Longpoll is closing itself"),this.shutdown_(),this.onDisconnect_&&(this.onDisconnect_(this.everConnected_),this.onDisconnect_=null))}close(){this.isClosed_||(this.log_("Longpoll is being closed."),this.shutdown_())}send(e){const t=O(e);this.bytesSent+=t.length,this.stats_.incrementCounter("bytes_sent",t.length);const i=rs(t),s=Hs(i,Cc);for(let r=0;r<s.length;r++)this.scriptTagHolder.enqueueSegment(this.curSegmentNum,s.length,s[r]),this.curSegmentNum++}addDisconnectPingFrame(e,t){this.myDisconnFrame=document.createElement("iframe");const i={};i[bc]="t",i[ir]=e,i[sr]=t,this.myDisconnFrame.src=this.urlFn(i),this.myDisconnFrame.style.display="none",document.body.appendChild(this.myDisconnFrame)}incrementIncomingBytes_(e){const t=O(e).length;this.bytesReceived+=t,this.stats_.incrementCounter("bytes_received",t)}}class ei{constructor(e,t,i,s){this.onDisconnect=i,this.urlFn=s,this.outstandingRequests=new Set,this.pendingSegs=[],this.currentSerial=Math.floor(Math.random()*1e8),this.sendNewPolls=!0;{this.uniqueCallbackIdentifier=Bs(),window[_c+this.uniqueCallbackIdentifier]=e,window[gc+this.uniqueCallbackIdentifier]=t,this.myIFrame=ei.createIFrame_();let r="";this.myIFrame.src&&this.myIFrame.src.substr(0,11)==="javascript:"&&(r='<script>document.domain="'+document.domain+'";<\/script>');const o="<html><body>"+r+"</body></html>";try{this.myIFrame.doc.open(),this.myIFrame.doc.write(o),this.myIFrame.doc.close()}catch(a){M("frame writing exception"),a.stack&&M(a.stack),M(a)}}}static createIFrame_(){const e=document.createElement("iframe");if(e.style.display="none",document.body){document.body.appendChild(e);try{e.contentWindow.document||M("No IE domain setting required")}catch{const i=document.domain;e.src="javascript:void((function(){document.open();document.domain='"+i+"';document.close();})())"}}else throw"Document body has not initialized. Wait to initialize Firebase until after the document is ready.";return e.contentDocument?e.doc=e.contentDocument:e.contentWindow?e.doc=e.contentWindow.document:e.document&&(e.doc=e.document),e}close(){this.alive=!1,this.myIFrame&&(this.myIFrame.doc.body.textContent="",setTimeout(()=>{this.myIFrame!==null&&(document.body.removeChild(this.myIFrame),this.myIFrame=null)},Math.floor(0)));const e=this.onDisconnect;e&&(this.onDisconnect=null,e())}startLongPoll(e,t){for(this.myID=e,this.myPW=t,this.alive=!0;this.newRequest_(););}newRequest_(){if(this.alive&&this.sendNewPolls&&this.outstandingRequests.size<(this.pendingSegs.length>0?2:1)){this.currentSerial++;const e={};e[ir]=this.myID,e[sr]=this.myPW,e[rr]=this.currentSerial;let t=this.urlFn(e),i="",s=0;for(;this.pendingSegs.length>0&&this.pendingSegs[0].d.length+ar+i.length<=or;){const o=this.pendingSegs.shift();i=i+"&"+yc+s+"="+o.seg+"&"+vc+s+"="+o.ts+"&"+wc+s+"="+o.d,s++}return t=t+i,this.addLongPollTag_(t,this.currentSerial),!0}else return!1}enqueueSegment(e,t,i){this.pendingSegs.push({seg:e,ts:t,d:i}),this.alive&&this.newRequest_()}addLongPollTag_(e,t){this.outstandingRequests.add(t);const i=()=>{this.outstandingRequests.delete(t),this.newRequest_()},s=setTimeout(i,Math.floor(Ec)),r=()=>{clearTimeout(s),i()};this.addTag(e,r)}addTag(e,t){setTimeout(()=>{try{if(!this.sendNewPolls)return;const i=this.myIFrame.doc.createElement("script");i.type="text/javascript",i.async=!0,i.src=e,i.onload=i.onreadystatechange=function(){const s=i.readyState;(!s||s==="loaded"||s==="complete")&&(i.onload=i.onreadystatechange=null,i.parentNode&&i.parentNode.removeChild(i),t())},i.onerror=()=>{M("Long-poll script failed to load: "+e),this.sendNewPolls=!1,this.close()},this.myIFrame.doc.body.appendChild(i)}catch{}},Math.floor(1))}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Ic=16384,xc=45e3;let jt=null;typeof MozWebSocket<"u"?jt=MozWebSocket:typeof WebSocket<"u"&&(jt=WebSocket);class q{constructor(e,t,i,s,r,o,a){this.connId=e,this.applicationId=i,this.appCheckToken=s,this.authToken=r,this.keepaliveTimer=null,this.frames=null,this.totalFrames=0,this.bytesSent=0,this.bytesReceived=0,this.log_=ft(this.connId),this.stats_=Zn(t),this.connURL=q.connectionURL_(t,o,a,s,i),this.nodeAdmin=t.nodeAdmin}static connectionURL_(e,t,i,s,r){const o={};return o[js]=Qn,typeof location<"u"&&location.hostname&&Qs.test(location.hostname)&&(o[qs]=Ks),t&&(o[Gs]=t),i&&(o[Ys]=i),s&&(o[Yn]=s),r&&(o[Js]=r),tr(e,Xs,o)}open(e,t){this.onDisconnect=t,this.onMessage=e,this.log_("Websocket connecting to "+this.connURL),this.everConnected_=!1,Oe.set("previous_websocket_failure",!0);try{let i;Ea(),this.mySock=new jt(this.connURL,[],i)}catch(i){this.log_("Error instantiating WebSocket.");const s=i.message||i.data;s&&this.log_(s),this.onClosed_();return}this.mySock.onopen=()=>{this.log_("Websocket connected."),this.everConnected_=!0},this.mySock.onclose=()=>{this.log_("Websocket connection was disconnected."),this.mySock=null,this.onClosed_()},this.mySock.onmessage=i=>{this.handleIncomingFrame(i)},this.mySock.onerror=i=>{this.log_("WebSocket error.  Closing connection.");const s=i.message||i.data;s&&this.log_(s),this.onClosed_()}}start(){}static forceDisallow(){q.forceDisallow_=!0}static isAvailable(){let e=!1;if(typeof navigator<"u"&&navigator.userAgent){const t=/Android ([0-9]{0,}\.[0-9]{0,})/,i=navigator.userAgent.match(t);i&&i.length>1&&parseFloat(i[1])<4.4&&(e=!0)}return!e&&jt!==null&&!q.forceDisallow_}static previouslyFailed(){return Oe.isInMemoryStorage||Oe.get("previous_websocket_failure")===!0}markConnectionHealthy(){Oe.remove("previous_websocket_failure")}appendFrame_(e){if(this.frames.push(e),this.frames.length===this.totalFrames){const t=this.frames.join("");this.frames=null;const i=lt(t);this.onMessage(i)}}handleNewFrameCount_(e){this.totalFrames=e,this.frames=[]}extractFrameCount_(e){if(f(this.frames===null,"We already have a frame buffer"),e.length<=6){const t=Number(e);if(!isNaN(t))return this.handleNewFrameCount_(t),null}return this.handleNewFrameCount_(1),e}handleIncomingFrame(e){if(this.mySock===null)return;const t=e.data;if(this.bytesReceived+=t.length,this.stats_.incrementCounter("bytes_received",t.length),this.resetKeepAlive(),this.frames!==null)this.appendFrame_(t);else{const i=this.extractFrameCount_(t);i!==null&&this.appendFrame_(i)}}send(e){this.resetKeepAlive();const t=O(e);this.bytesSent+=t.length,this.stats_.incrementCounter("bytes_sent",t.length);const i=Hs(t,Ic);i.length>1&&this.sendString_(String(i.length));for(let s=0;s<i.length;s++)this.sendString_(i[s])}shutdown_(){this.isClosed_=!0,this.keepaliveTimer&&(clearInterval(this.keepaliveTimer),this.keepaliveTimer=null),this.mySock&&(this.mySock.close(),this.mySock=null)}onClosed_(){this.isClosed_||(this.log_("WebSocket is closing itself"),this.shutdown_(),this.onDisconnect&&(this.onDisconnect(this.everConnected_),this.onDisconnect=null))}close(){this.isClosed_||(this.log_("WebSocket is being closed"),this.shutdown_())}resetKeepAlive(){clearInterval(this.keepaliveTimer),this.keepaliveTimer=setInterval(()=>{this.mySock&&this.sendString_("0"),this.resetKeepAlive()},Math.floor(xc))}sendString_(e){try{this.mySock.send(e)}catch(t){this.log_("Exception thrown from WebSocket.send():",t.message||t.data,"Closing connection."),setTimeout(this.onClosed_.bind(this),0)}}}q.responsesRequiredToBeHealthy=2,q.healthyTimeout=3e4;/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Je{static get ALL_TRANSPORTS(){return[ve,q]}static get IS_TRANSPORT_INITIALIZED(){return this.globalTransportInitialized_}constructor(e){this.initTransports_(e)}initTransports_(e){const t=q&&q.isAvailable();let i=t&&!q.previouslyFailed();if(e.webSocketOnly&&(t||$("wss:// URL used, but browser isn't known to support websockets.  Trying anyway."),i=!0),i)this.transports_=[q];else{const s=this.transports_=[];for(const r of Je.ALL_TRANSPORTS)r&&r.isAvailable()&&s.push(r);Je.globalTransportInitialized_=!0}}initialTransport(){if(this.transports_.length>0)return this.transports_[0];throw new Error("No transports available")}upgradeTransport(){return this.transports_.length>1?this.transports_[1]:null}}Je.globalTransportInitialized_=!1;/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Tc=6e4,kc=5e3,Ac=10*1024,Nc=100*1024,ti="t",lr="d",Rc="s",cr="r",Pc="e",hr="o",dr="a",ur="n",fr="p",Dc="h";class Oc{constructor(e,t,i,s,r,o,a,l,c,d){this.id=e,this.repoInfo_=t,this.applicationId_=i,this.appCheckToken_=s,this.authToken_=r,this.onMessage_=o,this.onReady_=a,this.onDisconnect_=l,this.onKill_=c,this.lastSessionId=d,this.connectionCount=0,this.pendingDataMessages=[],this.state_=0,this.log_=ft("c:"+this.id+":"),this.transportManager_=new Je(t),this.log_("Connection created"),this.start_()}start_(){const e=this.transportManager_.initialTransport();this.conn_=new e(this.nextTransportId_(),this.repoInfo_,this.applicationId_,this.appCheckToken_,this.authToken_,null,this.lastSessionId),this.primaryResponsesRequired_=e.responsesRequiredToBeHealthy||0;const t=this.connReceiver_(this.conn_),i=this.disconnReceiver_(this.conn_);this.tx_=this.conn_,this.rx_=this.conn_,this.secondaryConn_=null,this.isHealthy_=!1,setTimeout(()=>{this.conn_&&this.conn_.open(t,i)},Math.floor(0));const s=e.healthyTimeout||0;s>0&&(this.healthyTimeout_=_t(()=>{this.healthyTimeout_=null,this.isHealthy_||(this.conn_&&this.conn_.bytesReceived>Nc?(this.log_("Connection exceeded healthy timeout but has received "+this.conn_.bytesReceived+" bytes.  Marking connection healthy."),this.isHealthy_=!0,this.conn_.markConnectionHealthy()):this.conn_&&this.conn_.bytesSent>Ac?this.log_("Connection exceeded healthy timeout but has sent "+this.conn_.bytesSent+" bytes.  Leaving connection alive."):(this.log_("Closing unhealthy connection after timeout."),this.close()))},Math.floor(s)))}nextTransportId_(){return"c:"+this.id+":"+this.connectionCount++}disconnReceiver_(e){return t=>{e===this.conn_?this.onConnectionLost_(t):e===this.secondaryConn_?(this.log_("Secondary connection lost."),this.onSecondaryConnectionLost_()):this.log_("closing an old connection")}}connReceiver_(e){return t=>{this.state_!==2&&(e===this.rx_?this.onPrimaryMessageReceived_(t):e===this.secondaryConn_?this.onSecondaryMessageReceived_(t):this.log_("message on old connection"))}}sendRequest(e){const t={t:"d",d:e};this.sendData_(t)}tryCleanupConnection(){this.tx_===this.secondaryConn_&&this.rx_===this.secondaryConn_&&(this.log_("cleaning up and promoting a connection: "+this.secondaryConn_.connId),this.conn_=this.secondaryConn_,this.secondaryConn_=null)}onSecondaryControl_(e){if(ti in e){const t=e[ti];t===dr?this.upgradeIfSecondaryHealthy_():t===cr?(this.log_("Got a reset on secondary, closing it"),this.secondaryConn_.close(),(this.tx_===this.secondaryConn_||this.rx_===this.secondaryConn_)&&this.close()):t===hr&&(this.log_("got pong on secondary."),this.secondaryResponsesRequired_--,this.upgradeIfSecondaryHealthy_())}}onSecondaryMessageReceived_(e){const t=pt("t",e),i=pt("d",e);if(t==="c")this.onSecondaryControl_(i);else if(t==="d")this.pendingDataMessages.push(i);else throw new Error("Unknown protocol layer: "+t)}upgradeIfSecondaryHealthy_(){this.secondaryResponsesRequired_<=0?(this.log_("Secondary connection is healthy."),this.isHealthy_=!0,this.secondaryConn_.markConnectionHealthy(),this.proceedWithUpgrade_()):(this.log_("sending ping on secondary."),this.secondaryConn_.send({t:"c",d:{t:fr,d:{}}}))}proceedWithUpgrade_(){this.secondaryConn_.start(),this.log_("sending client ack on secondary"),this.secondaryConn_.send({t:"c",d:{t:dr,d:{}}}),this.log_("Ending transmission on primary"),this.conn_.send({t:"c",d:{t:ur,d:{}}}),this.tx_=this.secondaryConn_,this.tryCleanupConnection()}onPrimaryMessageReceived_(e){const t=pt("t",e),i=pt("d",e);t==="c"?this.onControl_(i):t==="d"&&this.onDataMessage_(i)}onDataMessage_(e){this.onPrimaryResponse_(),this.onMessage_(e)}onPrimaryResponse_(){this.isHealthy_||(this.primaryResponsesRequired_--,this.primaryResponsesRequired_<=0&&(this.log_("Primary connection is healthy."),this.isHealthy_=!0,this.conn_.markConnectionHealthy()))}onControl_(e){const t=pt(ti,e);if(lr in e){const i=e[lr];if(t===Dc){const s={...i};this.repoInfo_.isUsingEmulator&&(s.h=this.repoInfo_.host),this.onHandshake_(s)}else if(t===ur){this.log_("recvd end transmission on primary"),this.rx_=this.secondaryConn_;for(let s=0;s<this.pendingDataMessages.length;++s)this.onDataMessage_(this.pendingDataMessages[s]);this.pendingDataMessages=[],this.tryCleanupConnection()}else t===Rc?this.onConnectionShutdown_(i):t===cr?this.onReset_(i):t===Pc?qn("Server Error: "+i):t===hr?(this.log_("got pong on primary."),this.onPrimaryResponse_(),this.sendPingOnPrimaryIfNecessary_()):qn("Unknown control packet command: "+t)}}onHandshake_(e){const t=e.ts,i=e.v,s=e.h;this.sessionId=e.s,this.repoInfo_.host=s,this.state_===0&&(this.conn_.start(),this.onConnectionEstablished_(this.conn_,t),Qn!==i&&$("Protocol version mismatch detected"),this.tryStartUpgrade_())}tryStartUpgrade_(){const e=this.transportManager_.upgradeTransport();e&&this.startUpgrade_(e)}startUpgrade_(e){this.secondaryConn_=new e(this.nextTransportId_(),this.repoInfo_,this.applicationId_,this.appCheckToken_,this.authToken_,this.sessionId),this.secondaryResponsesRequired_=e.responsesRequiredToBeHealthy||0;const t=this.connReceiver_(this.secondaryConn_),i=this.disconnReceiver_(this.secondaryConn_);this.secondaryConn_.open(t,i),_t(()=>{this.secondaryConn_&&(this.log_("Timed out trying to upgrade."),this.secondaryConn_.close())},Math.floor(Tc))}onReset_(e){this.log_("Reset packet received.  New host: "+e),this.repoInfo_.host=e,this.state_===1?this.close():(this.closeConnections_(),this.start_())}onConnectionEstablished_(e,t){this.log_("Realtime connection established."),this.conn_=e,this.state_=1,this.onReady_&&(this.onReady_(t,this.sessionId),this.onReady_=null),this.primaryResponsesRequired_===0?(this.log_("Primary connection is healthy."),this.isHealthy_=!0):_t(()=>{this.sendPingOnPrimaryIfNecessary_()},Math.floor(kc))}sendPingOnPrimaryIfNecessary_(){!this.isHealthy_&&this.state_===1&&(this.log_("sending ping on primary."),this.sendData_({t:"c",d:{t:fr,d:{}}}))}onSecondaryConnectionLost_(){const e=this.secondaryConn_;this.secondaryConn_=null,(this.tx_===e||this.rx_===e)&&this.close()}onConnectionLost_(e){this.conn_=null,!e&&this.state_===0?(this.log_("Realtime connection failed."),this.repoInfo_.isCacheableHost()&&(Oe.remove("host:"+this.repoInfo_.host),this.repoInfo_.internalHost=this.repoInfo_.host)):this.state_===1&&this.log_("Realtime connection lost."),this.close()}onConnectionShutdown_(e){this.log_("Connection shutdown command received. Shutting down..."),this.onKill_&&(this.onKill_(e),this.onKill_=null),this.onDisconnect_=null,this.close()}sendData_(e){if(this.state_!==1)throw"Connection is not connected";this.tx_.send(e)}close(){this.state_!==2&&(this.log_("Closing realtime connection."),this.state_=2,this.closeConnections_(),this.onDisconnect_&&(this.onDisconnect_(),this.onDisconnect_=null))}closeConnections_(){this.log_("Shutting down all connections"),this.conn_&&(this.conn_.close(),this.conn_=null),this.secondaryConn_&&(this.secondaryConn_.close(),this.secondaryConn_=null),this.healthyTimeout_&&(clearTimeout(this.healthyTimeout_),this.healthyTimeout_=null)}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class pr{put(e,t,i,s){}merge(e,t,i,s){}refreshAuthToken(e){}refreshAppCheckToken(e){}onDisconnectPut(e,t,i){}onDisconnectMerge(e,t,i){}onDisconnectCancel(e,t){}reportStats(e){}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class _r{constructor(e){this.allowedEvents_=e,this.listeners_={},f(Array.isArray(e)&&e.length>0,"Requires a non-empty array")}trigger(e,...t){if(Array.isArray(this.listeners_[e])){const i=[...this.listeners_[e]];for(let s=0;s<i.length;s++)i[s].callback.apply(i[s].context,t)}}on(e,t,i){this.validateEventType_(e),this.listeners_[e]=this.listeners_[e]||[],this.listeners_[e].push({callback:t,context:i});const s=this.getInitialEvent(e);s&&t.apply(i,s)}off(e,t,i){this.validateEventType_(e);const s=this.listeners_[e]||[];for(let r=0;r<s.length;r++)if(s[r].callback===t&&(!i||i===s[r].context)){s.splice(r,1);return}}validateEventType_(e){f(this.allowedEvents_.find(t=>t===e),"Unknown event: "+e)}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Gt extends _r{static getInstance(){return new Gt}constructor(){super(["online"]),this.online_=!0,typeof window<"u"&&typeof window.addEventListener<"u"&&!ls()&&(window.addEventListener("online",()=>{this.online_||(this.online_=!0,this.trigger("online",!0))},!1),window.addEventListener("offline",()=>{this.online_&&(this.online_=!1,this.trigger("online",!1))},!1))}getInitialEvent(e){return f(e==="online","Unknown event type: "+e),[this.online_]}currentlyOnline(){return this.online_}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const gr=32,mr=768;class S{constructor(e,t){if(t===void 0){this.pieces_=e.split("/");let i=0;for(let s=0;s<this.pieces_.length;s++)this.pieces_[s].length>0&&(this.pieces_[i]=this.pieces_[s],i++);this.pieces_.length=i,this.pieceNum_=0}else this.pieces_=e,this.pieceNum_=t}toString(){let e="";for(let t=this.pieceNum_;t<this.pieces_.length;t++)this.pieces_[t]!==""&&(e+="/"+this.pieces_[t]);return e||"/"}}function E(){return new S("")}function v(n){return n.pieceNum_>=n.pieces_.length?null:n.pieces_[n.pieceNum_]}function we(n){return n.pieces_.length-n.pieceNum_}function T(n){let e=n.pieceNum_;return e<n.pieces_.length&&e++,new S(n.pieces_,e)}function ni(n){return n.pieceNum_<n.pieces_.length?n.pieces_[n.pieces_.length-1]:null}function Mc(n){let e="";for(let t=n.pieceNum_;t<n.pieces_.length;t++)n.pieces_[t]!==""&&(e+="/"+encodeURIComponent(String(n.pieces_[t])));return e||"/"}function gt(n,e=0){return n.pieces_.slice(n.pieceNum_+e)}function yr(n){if(n.pieceNum_>=n.pieces_.length)return null;const e=[];for(let t=n.pieceNum_;t<n.pieces_.length-1;t++)e.push(n.pieces_[t]);return new S(e,0)}function R(n,e){const t=[];for(let i=n.pieceNum_;i<n.pieces_.length;i++)t.push(n.pieces_[i]);if(e instanceof S)for(let i=e.pieceNum_;i<e.pieces_.length;i++)t.push(e.pieces_[i]);else{const i=e.split("/");for(let s=0;s<i.length;s++)i[s].length>0&&t.push(i[s])}return new S(t,0)}function w(n){return n.pieceNum_>=n.pieces_.length}function W(n,e){const t=v(n),i=v(e);if(t===null)return e;if(t===i)return W(T(n),T(e));throw new Error("INTERNAL ERROR: innerPath ("+e+") is not within outerPath ("+n+")")}function Lc(n,e){const t=gt(n,0),i=gt(e,0);for(let s=0;s<t.length&&s<i.length;s++){const r=Le(t[s],i[s]);if(r!==0)return r}return t.length===i.length?0:t.length<i.length?-1:1}function ii(n,e){if(we(n)!==we(e))return!1;for(let t=n.pieceNum_,i=e.pieceNum_;t<=n.pieces_.length;t++,i++)if(n.pieces_[t]!==e.pieces_[i])return!1;return!0}function K(n,e){let t=n.pieceNum_,i=e.pieceNum_;if(we(n)>we(e))return!1;for(;t<n.pieces_.length;){if(n.pieces_[t]!==e.pieces_[i])return!1;++t,++i}return!0}class Fc{constructor(e,t){this.errorPrefix_=t,this.parts_=gt(e,0),this.byteLength_=Math.max(1,this.parts_.length);for(let i=0;i<this.parts_.length;i++)this.byteLength_+=Ht(this.parts_[i]);vr(this)}}function Bc(n,e){n.parts_.length>0&&(n.byteLength_+=1),n.parts_.push(e),n.byteLength_+=Ht(e),vr(n)}function $c(n){const e=n.parts_.pop();n.byteLength_-=Ht(e),n.parts_.length>0&&(n.byteLength_-=1)}function vr(n){if(n.byteLength_>mr)throw new Error(n.errorPrefix_+"has a key path longer than "+mr+" bytes ("+n.byteLength_+").");if(n.parts_.length>gr)throw new Error(n.errorPrefix_+"path specified exceeds the maximum depth that can be written ("+gr+") or object contains a cycle "+Fe(n))}function Fe(n){return n.parts_.length===0?"":"in property '"+n.parts_.join(".")+"'"}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class si extends _r{static getInstance(){return new si}constructor(){super(["visible"]);let e,t;typeof document<"u"&&typeof document.addEventListener<"u"&&(typeof document.hidden<"u"?(t="visibilitychange",e="hidden"):typeof document.mozHidden<"u"?(t="mozvisibilitychange",e="mozHidden"):typeof document.msHidden<"u"?(t="msvisibilitychange",e="msHidden"):typeof document.webkitHidden<"u"&&(t="webkitvisibilitychange",e="webkitHidden")),this.visible_=!0,t&&document.addEventListener(t,()=>{const i=!document[e];i!==this.visible_&&(this.visible_=i,this.trigger("visible",i))},!1)}getInitialEvent(e){return f(e==="visible","Unknown event type: "+e),[this.visible_]}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const mt=1e3,Wc=300*1e3,wr=30*1e3,Uc=1.3,Hc=3e4,zc="server_kill",br=3;class Q extends pr{constructor(e,t,i,s,r,o,a,l){if(super(),this.repoInfo_=e,this.applicationId_=t,this.onDataUpdate_=i,this.onConnectStatus_=s,this.onServerInfoUpdate_=r,this.authTokenProvider_=o,this.appCheckTokenProvider_=a,this.authOverride_=l,this.id=Q.nextPersistentConnectionId_++,this.log_=ft("p:"+this.id+":"),this.interruptReasons_={},this.listens=new Map,this.outstandingPuts_=[],this.outstandingGets_=[],this.outstandingPutCount_=0,this.outstandingGetCount_=0,this.onDisconnectRequestQueue_=[],this.connected_=!1,this.reconnectDelay_=mt,this.maxReconnectDelay_=Wc,this.securityDebugCallback_=null,this.lastSessionId=null,this.establishConnectionTimer_=null,this.visible_=!1,this.requestCBHash_={},this.requestNumber_=0,this.realtime_=null,this.authToken_=null,this.appCheckToken_=null,this.forceTokenRefresh_=!1,this.invalidAuthTokenCount_=0,this.invalidAppCheckTokenCount_=0,this.firstConnection_=!0,this.lastConnectionAttemptTime_=null,this.lastConnectionEstablishedTime_=null,l)throw new Error("Auth override specified in options, but not supported on non Node.js platforms");si.getInstance().on("visible",this.onVisible_,this),e.host.indexOf("fblocal")===-1&&Gt.getInstance().on("online",this.onOnline_,this)}sendRequest(e,t,i){const s=++this.requestNumber_,r={r:s,a:e,b:t};this.log_(O(r)),f(this.connected_,"sendRequest call when we're not connected not allowed."),this.realtime_.sendRequest(r),i&&(this.requestCBHash_[s]=i)}get(e){this.initConnection_();const t=new j,s={action:"g",request:{p:e._path.toString(),q:e._queryObject},onComplete:o=>{const a=o.d;o.s==="ok"?t.resolve(a):t.reject(a)}};this.outstandingGets_.push(s),this.outstandingGetCount_++;const r=this.outstandingGets_.length-1;return this.connected_&&this.sendGet_(r),t.promise}listen(e,t,i,s){this.initConnection_();const r=e._queryIdentifier,o=e._path.toString();this.log_("Listen called for "+o+" "+r),this.listens.has(o)||this.listens.set(o,new Map),f(e._queryParams.isDefault()||!e._queryParams.loadsAllData(),"listen() called for non-default but complete query"),f(!this.listens.get(o).has(r),"listen() called twice for same path/queryId.");const a={onComplete:s,hashFn:t,query:e,tag:i};this.listens.get(o).set(r,a),this.connected_&&this.sendListen_(a)}sendGet_(e){const t=this.outstandingGets_[e];this.sendRequest("g",t.request,i=>{delete this.outstandingGets_[e],this.outstandingGetCount_--,this.outstandingGetCount_===0&&(this.outstandingGets_=[]),t.onComplete&&t.onComplete(i)})}sendListen_(e){const t=e.query,i=t._path.toString(),s=t._queryIdentifier;this.log_("Listen on "+i+" for "+s);const r={p:i},o="q";e.tag&&(r.q=t._queryObject,r.t=e.tag),r.h=e.hashFn(),this.sendRequest(o,r,a=>{const l=a.d,c=a.s;Q.warnOnListenWarnings_(l,t),(this.listens.get(i)&&this.listens.get(i).get(s))===e&&(this.log_("listen response",a),c!=="ok"&&this.removeListen_(i,s),e.onComplete&&e.onComplete(c,l))})}static warnOnListenWarnings_(e,t){if(e&&typeof e=="object"&&X(e,"w")){const i=Ne(e,"w");if(Array.isArray(i)&&~i.indexOf("no_index")){const s='".indexOn": "'+t._queryParams.getIndex().toString()+'"',r=t._path.toString();$(`Using an unspecified index. Your data will be downloaded and filtered on the client. Consider adding ${s} at ${r} to your security rules for better performance.`)}}}refreshAuthToken(e){this.authToken_=e,this.log_("Auth token refreshed"),this.authToken_?this.tryAuth():this.connected_&&this.sendRequest("unauth",{},()=>{}),this.reduceReconnectDelayIfAdminCredential_(e)}reduceReconnectDelayIfAdminCredential_(e){(e&&e.length===40||Aa(e))&&(this.log_("Admin auth credential detected.  Reducing max reconnect time."),this.maxReconnectDelay_=wr)}refreshAppCheckToken(e){this.appCheckToken_=e,this.log_("App check token refreshed"),this.appCheckToken_?this.tryAppCheck():this.connected_&&this.sendRequest("unappeck",{},()=>{})}tryAuth(){if(this.connected_&&this.authToken_){const e=this.authToken_,t=ka(e)?"auth":"gauth",i={cred:e};this.authOverride_===null?i.noauth=!0:typeof this.authOverride_=="object"&&(i.authvar=this.authOverride_),this.sendRequest(t,i,s=>{const r=s.s,o=s.d||"error";this.authToken_===e&&(r==="ok"?this.invalidAuthTokenCount_=0:this.onAuthRevoked_(r,o))})}}tryAppCheck(){this.connected_&&this.appCheckToken_&&this.sendRequest("appcheck",{token:this.appCheckToken_},e=>{const t=e.s,i=e.d||"error";t==="ok"?this.invalidAppCheckTokenCount_=0:this.onAppCheckRevoked_(t,i)})}unlisten(e,t){const i=e._path.toString(),s=e._queryIdentifier;this.log_("Unlisten called for "+i+" "+s),f(e._queryParams.isDefault()||!e._queryParams.loadsAllData(),"unlisten() called for non-default but complete query"),this.removeListen_(i,s)&&this.connected_&&this.sendUnlisten_(i,s,e._queryObject,t)}sendUnlisten_(e,t,i,s){this.log_("Unlisten on "+e+" for "+t);const r={p:e},o="n";s&&(r.q=i,r.t=s),this.sendRequest(o,r)}onDisconnectPut(e,t,i){this.initConnection_(),this.connected_?this.sendOnDisconnect_("o",e,t,i):this.onDisconnectRequestQueue_.push({pathString:e,action:"o",data:t,onComplete:i})}onDisconnectMerge(e,t,i){this.initConnection_(),this.connected_?this.sendOnDisconnect_("om",e,t,i):this.onDisconnectRequestQueue_.push({pathString:e,action:"om",data:t,onComplete:i})}onDisconnectCancel(e,t){this.initConnection_(),this.connected_?this.sendOnDisconnect_("oc",e,null,t):this.onDisconnectRequestQueue_.push({pathString:e,action:"oc",data:null,onComplete:t})}sendOnDisconnect_(e,t,i,s){const r={p:t,d:i};this.log_("onDisconnect "+e,r),this.sendRequest(e,r,o=>{s&&setTimeout(()=>{s(o.s,o.d)},Math.floor(0))})}put(e,t,i,s){this.putInternal("p",e,t,i,s)}merge(e,t,i,s){this.putInternal("m",e,t,i,s)}putInternal(e,t,i,s,r){this.initConnection_();const o={p:t,d:i};r!==void 0&&(o.h=r),this.outstandingPuts_.push({action:e,request:o,onComplete:s}),this.outstandingPutCount_++;const a=this.outstandingPuts_.length-1;this.connected_?this.sendPut_(a):this.log_("Buffering put: "+t)}sendPut_(e){const t=this.outstandingPuts_[e].action,i=this.outstandingPuts_[e].request,s=this.outstandingPuts_[e].onComplete;this.outstandingPuts_[e].queued=this.connected_,this.sendRequest(t,i,r=>{this.log_(t+" response",r),delete this.outstandingPuts_[e],this.outstandingPutCount_--,this.outstandingPutCount_===0&&(this.outstandingPuts_=[]),s&&s(r.s,r.d)})}reportStats(e){if(this.connected_){const t={c:e};this.log_("reportStats",t),this.sendRequest("s",t,i=>{if(i.s!=="ok"){const r=i.d;this.log_("reportStats","Error sending stats: "+r)}})}}onDataMessage_(e){if("r"in e){this.log_("from server: "+O(e));const t=e.r,i=this.requestCBHash_[t];i&&(delete this.requestCBHash_[t],i(e.b))}else{if("error"in e)throw"A server-side error has occurred: "+e.error;"a"in e&&this.onDataPush_(e.a,e.b)}}onDataPush_(e,t){this.log_("handleServerMessage",e,t),e==="d"?this.onDataUpdate_(t.p,t.d,!1,t.t):e==="m"?this.onDataUpdate_(t.p,t.d,!0,t.t):e==="c"?this.onListenRevoked_(t.p,t.q):e==="ac"?this.onAuthRevoked_(t.s,t.d):e==="apc"?this.onAppCheckRevoked_(t.s,t.d):e==="sd"?this.onSecurityDebugPacket_(t):qn("Unrecognized action received from server: "+O(e)+`
Are you using the latest client?`)}onReady_(e,t){this.log_("connection ready"),this.connected_=!0,this.lastConnectionEstablishedTime_=new Date().getTime(),this.handleTimestamp_(e),this.lastSessionId=t,this.firstConnection_&&this.sendConnectStats_(),this.restoreState_(),this.firstConnection_=!1,this.onConnectStatus_(!0)}scheduleConnect_(e){f(!this.realtime_,"Scheduling a connect when we're already connected/ing?"),this.establishConnectionTimer_&&clearTimeout(this.establishConnectionTimer_),this.establishConnectionTimer_=setTimeout(()=>{this.establishConnectionTimer_=null,this.establishConnection_()},Math.floor(e))}initConnection_(){!this.realtime_&&this.firstConnection_&&this.scheduleConnect_(0)}onVisible_(e){e&&!this.visible_&&this.reconnectDelay_===this.maxReconnectDelay_&&(this.log_("Window became visible.  Reducing delay."),this.reconnectDelay_=mt,this.realtime_||this.scheduleConnect_(0)),this.visible_=e}onOnline_(e){e?(this.log_("Browser went online."),this.reconnectDelay_=mt,this.realtime_||this.scheduleConnect_(0)):(this.log_("Browser went offline.  Killing connection."),this.realtime_&&this.realtime_.close())}onRealtimeDisconnect_(){if(this.log_("data client disconnected"),this.connected_=!1,this.realtime_=null,this.cancelSentTransactions_(),this.requestCBHash_={},this.shouldReconnect_()){this.visible_?this.lastConnectionEstablishedTime_&&(new Date().getTime()-this.lastConnectionEstablishedTime_>Hc&&(this.reconnectDelay_=mt),this.lastConnectionEstablishedTime_=null):(this.log_("Window isn't visible.  Delaying reconnect."),this.reconnectDelay_=this.maxReconnectDelay_,this.lastConnectionAttemptTime_=new Date().getTime());const e=Math.max(0,new Date().getTime()-this.lastConnectionAttemptTime_);let t=Math.max(0,this.reconnectDelay_-e);t=Math.random()*t,this.log_("Trying to reconnect in "+t+"ms"),this.scheduleConnect_(t),this.reconnectDelay_=Math.min(this.maxReconnectDelay_,this.reconnectDelay_*Uc)}this.onConnectStatus_(!1)}async establishConnection_(){if(this.shouldReconnect_()){this.log_("Making a connection attempt"),this.lastConnectionAttemptTime_=new Date().getTime(),this.lastConnectionEstablishedTime_=null;const e=this.onDataMessage_.bind(this),t=this.onReady_.bind(this),i=this.onRealtimeDisconnect_.bind(this),s=this.id+":"+Q.nextConnectionId_++,r=this.lastSessionId;let o=!1,a=null;const l=function(){a?a.close():(o=!0,i())},c=function(h){f(a,"sendRequest call when we're not connected not allowed."),a.sendRequest(h)};this.realtime_={close:l,sendRequest:c};const d=this.forceTokenRefresh_;this.forceTokenRefresh_=!1;try{const[h,u]=await Promise.all([this.authTokenProvider_.getToken(d),this.appCheckTokenProvider_.getToken(d)]);o?M("getToken() completed but was canceled"):(M("getToken() completed. Creating connection."),this.authToken_=h&&h.accessToken,this.appCheckToken_=u&&u.token,a=new Oc(s,this.repoInfo_,this.applicationId_,this.appCheckToken_,this.authToken_,e,t,i,p=>{$(p+" ("+this.repoInfo_.toString()+")"),this.interrupt(zc)},r))}catch(h){this.log_("Failed to get token: "+h),o||(this.repoInfo_.nodeAdmin&&$(h),l())}}}interrupt(e){M("Interrupting connection for reason: "+e),this.interruptReasons_[e]=!0,this.realtime_?this.realtime_.close():(this.establishConnectionTimer_&&(clearTimeout(this.establishConnectionTimer_),this.establishConnectionTimer_=null),this.connected_&&this.onRealtimeDisconnect_())}resume(e){M("Resuming connection for reason: "+e),delete this.interruptReasons_[e],Nn(this.interruptReasons_)&&(this.reconnectDelay_=mt,this.realtime_||this.scheduleConnect_(0))}handleTimestamp_(e){const t=e-new Date().getTime();this.onServerInfoUpdate_({serverTimeOffset:t})}cancelSentTransactions_(){for(let e=0;e<this.outstandingPuts_.length;e++){const t=this.outstandingPuts_[e];t&&"h"in t.request&&t.queued&&(t.onComplete&&t.onComplete("disconnect"),delete this.outstandingPuts_[e],this.outstandingPutCount_--)}this.outstandingPutCount_===0&&(this.outstandingPuts_=[])}onListenRevoked_(e,t){let i;t?i=t.map(r=>Kn(r)).join("$"):i="default";const s=this.removeListen_(e,i);s&&s.onComplete&&s.onComplete("permission_denied")}removeListen_(e,t){const i=new S(e).toString();let s;if(this.listens.has(i)){const r=this.listens.get(i);s=r.get(t),r.delete(t),r.size===0&&this.listens.delete(i)}else s=void 0;return s}onAuthRevoked_(e,t){M("Auth token revoked: "+e+"/"+t),this.authToken_=null,this.forceTokenRefresh_=!0,this.realtime_.close(),(e==="invalid_token"||e==="permission_denied")&&(this.invalidAuthTokenCount_++,this.invalidAuthTokenCount_>=br&&(this.reconnectDelay_=wr,this.authTokenProvider_.notifyForInvalidToken()))}onAppCheckRevoked_(e,t){M("App check token revoked: "+e+"/"+t),this.appCheckToken_=null,this.forceTokenRefresh_=!0,(e==="invalid_token"||e==="permission_denied")&&(this.invalidAppCheckTokenCount_++,this.invalidAppCheckTokenCount_>=br&&this.appCheckTokenProvider_.notifyForInvalidToken())}onSecurityDebugPacket_(e){this.securityDebugCallback_?this.securityDebugCallback_(e):"msg"in e&&console.log("FIREBASE: "+e.msg.replace(`
`,`
FIREBASE: `))}restoreState_(){this.tryAuth(),this.tryAppCheck();for(const e of this.listens.values())for(const t of e.values())this.sendListen_(t);for(let e=0;e<this.outstandingPuts_.length;e++)this.outstandingPuts_[e]&&this.sendPut_(e);for(;this.onDisconnectRequestQueue_.length;){const e=this.onDisconnectRequestQueue_.shift();this.sendOnDisconnect_(e.action,e.pathString,e.data,e.onComplete)}for(let e=0;e<this.outstandingGets_.length;e++)this.outstandingGets_[e]&&this.sendGet_(e)}sendConnectStats_(){const e={};let t="js";e["sdk."+t+"."+Ls.replace(/\./g,"-")]=1,ls()?e["framework.cordova"]=1:Ca()&&(e["framework.reactnative"]=1),this.reportStats(e)}shouldReconnect_(){const e=Gt.getInstance().currentlyOnline();return Nn(this.interruptReasons_)&&e}}Q.nextPersistentConnectionId_=0,Q.nextConnectionId_=0;/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class b{constructor(e,t){this.name=e,this.node=t}static Wrap(e,t){return new b(e,t)}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class qt{getCompare(){return this.compare.bind(this)}indexedValueChanged(e,t){const i=new b(ye,e),s=new b(ye,t);return this.compare(i,s)!==0}minPost(){return b.MIN}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */let Kt;class Cr extends qt{static get __EMPTY_NODE(){return Kt}static set __EMPTY_NODE(e){Kt=e}compare(e,t){return Le(e.name,t.name)}isDefinedOn(e){throw Ve("KeyIndex.isDefinedOn not expected to be called.")}indexedValueChanged(e,t){return!1}minPost(){return b.MIN}maxPost(){return new b(de,Kt)}makePost(e,t){return f(typeof e=="string","KeyIndex indexValue must always be a string."),new b(e,Kt)}toString(){return".key"}}const re=new Cr;/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Qt{constructor(e,t,i,s,r=null){this.isReverse_=s,this.resultGenerator_=r,this.nodeStack_=[];let o=1;for(;!e.isEmpty();)if(e=e,o=t?i(e.key,t):1,s&&(o*=-1),o<0)this.isReverse_?e=e.left:e=e.right;else if(o===0){this.nodeStack_.push(e);break}else this.nodeStack_.push(e),this.isReverse_?e=e.right:e=e.left}getNext(){if(this.nodeStack_.length===0)return null;let e=this.nodeStack_.pop(),t;if(this.resultGenerator_?t=this.resultGenerator_(e.key,e.value):t={key:e.key,value:e.value},this.isReverse_)for(e=e.left;!e.isEmpty();)this.nodeStack_.push(e),e=e.right;else for(e=e.right;!e.isEmpty();)this.nodeStack_.push(e),e=e.left;return t}hasNext(){return this.nodeStack_.length>0}peek(){if(this.nodeStack_.length===0)return null;const e=this.nodeStack_[this.nodeStack_.length-1];return this.resultGenerator_?this.resultGenerator_(e.key,e.value):{key:e.key,value:e.value}}}class F{constructor(e,t,i,s,r){this.key=e,this.value=t,this.color=i??F.RED,this.left=s??z.EMPTY_NODE,this.right=r??z.EMPTY_NODE}copy(e,t,i,s,r){return new F(e??this.key,t??this.value,i??this.color,s??this.left,r??this.right)}count(){return this.left.count()+1+this.right.count()}isEmpty(){return!1}inorderTraversal(e){return this.left.inorderTraversal(e)||!!e(this.key,this.value)||this.right.inorderTraversal(e)}reverseTraversal(e){return this.right.reverseTraversal(e)||e(this.key,this.value)||this.left.reverseTraversal(e)}min_(){return this.left.isEmpty()?this:this.left.min_()}minKey(){return this.min_().key}maxKey(){return this.right.isEmpty()?this.key:this.right.maxKey()}insert(e,t,i){let s=this;const r=i(e,s.key);return r<0?s=s.copy(null,null,null,s.left.insert(e,t,i),null):r===0?s=s.copy(null,t,null,null,null):s=s.copy(null,null,null,null,s.right.insert(e,t,i)),s.fixUp_()}removeMin_(){if(this.left.isEmpty())return z.EMPTY_NODE;let e=this;return!e.left.isRed_()&&!e.left.left.isRed_()&&(e=e.moveRedLeft_()),e=e.copy(null,null,null,e.left.removeMin_(),null),e.fixUp_()}remove(e,t){let i,s;if(i=this,t(e,i.key)<0)!i.left.isEmpty()&&!i.left.isRed_()&&!i.left.left.isRed_()&&(i=i.moveRedLeft_()),i=i.copy(null,null,null,i.left.remove(e,t),null);else{if(i.left.isRed_()&&(i=i.rotateRight_()),!i.right.isEmpty()&&!i.right.isRed_()&&!i.right.left.isRed_()&&(i=i.moveRedRight_()),t(e,i.key)===0){if(i.right.isEmpty())return z.EMPTY_NODE;s=i.right.min_(),i=i.copy(s.key,s.value,null,null,i.right.removeMin_())}i=i.copy(null,null,null,null,i.right.remove(e,t))}return i.fixUp_()}isRed_(){return this.color}fixUp_(){let e=this;return e.right.isRed_()&&!e.left.isRed_()&&(e=e.rotateLeft_()),e.left.isRed_()&&e.left.left.isRed_()&&(e=e.rotateRight_()),e.left.isRed_()&&e.right.isRed_()&&(e=e.colorFlip_()),e}moveRedLeft_(){let e=this.colorFlip_();return e.right.left.isRed_()&&(e=e.copy(null,null,null,null,e.right.rotateRight_()),e=e.rotateLeft_(),e=e.colorFlip_()),e}moveRedRight_(){let e=this.colorFlip_();return e.left.left.isRed_()&&(e=e.rotateRight_(),e=e.colorFlip_()),e}rotateLeft_(){const e=this.copy(null,null,F.RED,null,this.right.left);return this.right.copy(null,null,this.color,e,null)}rotateRight_(){const e=this.copy(null,null,F.RED,this.left.right,null);return this.left.copy(null,null,this.color,null,e)}colorFlip_(){const e=this.left.copy(null,null,!this.left.color,null,null),t=this.right.copy(null,null,!this.right.color,null,null);return this.copy(null,null,!this.color,e,t)}checkMaxDepth_(){const e=this.check_();return Math.pow(2,e)<=this.count()+1}check_(){if(this.isRed_()&&this.left.isRed_())throw new Error("Red node has red child("+this.key+","+this.value+")");if(this.right.isRed_())throw new Error("Right child of ("+this.key+","+this.value+") is red");const e=this.left.check_();if(e!==this.right.check_())throw new Error("Black depths differ");return e+(this.isRed_()?0:1)}}F.RED=!0,F.BLACK=!1;class Vc{copy(e,t,i,s,r){return this}insert(e,t,i){return new F(e,t,null)}remove(e,t){return this}count(){return 0}isEmpty(){return!0}inorderTraversal(e){return!1}reverseTraversal(e){return!1}minKey(){return null}maxKey(){return null}check_(){return 0}isRed_(){return!1}}class z{constructor(e,t=z.EMPTY_NODE){this.comparator_=e,this.root_=t}insert(e,t){return new z(this.comparator_,this.root_.insert(e,t,this.comparator_).copy(null,null,F.BLACK,null,null))}remove(e){return new z(this.comparator_,this.root_.remove(e,this.comparator_).copy(null,null,F.BLACK,null,null))}get(e){let t,i=this.root_;for(;!i.isEmpty();){if(t=this.comparator_(e,i.key),t===0)return i.value;t<0?i=i.left:t>0&&(i=i.right)}return null}getPredecessorKey(e){let t,i=this.root_,s=null;for(;!i.isEmpty();)if(t=this.comparator_(e,i.key),t===0){if(i.left.isEmpty())return s?s.key:null;for(i=i.left;!i.right.isEmpty();)i=i.right;return i.key}else t<0?i=i.left:t>0&&(s=i,i=i.right);throw new Error("Attempted to find predecessor key for a nonexistent key.  What gives?")}isEmpty(){return this.root_.isEmpty()}count(){return this.root_.count()}minKey(){return this.root_.minKey()}maxKey(){return this.root_.maxKey()}inorderTraversal(e){return this.root_.inorderTraversal(e)}reverseTraversal(e){return this.root_.reverseTraversal(e)}getIterator(e){return new Qt(this.root_,null,this.comparator_,!1,e)}getIteratorFrom(e,t){return new Qt(this.root_,e,this.comparator_,!1,t)}getReverseIteratorFrom(e,t){return new Qt(this.root_,e,this.comparator_,!0,t)}getReverseIterator(e){return new Qt(this.root_,null,this.comparator_,!0,e)}}z.EMPTY_NODE=new Vc;/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function jc(n,e){return Le(n.name,e.name)}function ri(n,e){return Le(n,e)}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */let oi;function Gc(n){oi=n}const Er=function(n){return typeof n=="number"?"number:"+zs(n):"string:"+n},Sr=function(n){if(n.isLeafNode()){const e=n.val();f(typeof e=="string"||typeof e=="number"||typeof e=="object"&&X(e,".sv"),"Priority must be a string or number.")}else f(n===oi||n.isEmpty(),"priority of unexpected type.");f(n===oi||n.getPriority().isEmpty(),"Priority nodes can't have a priority of their own.")};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */let Ir;class B{static set __childrenNodeConstructor(e){Ir=e}static get __childrenNodeConstructor(){return Ir}constructor(e,t=B.__childrenNodeConstructor.EMPTY_NODE){this.value_=e,this.priorityNode_=t,this.lazyHash_=null,f(this.value_!==void 0&&this.value_!==null,"LeafNode shouldn't be created with null/undefined value."),Sr(this.priorityNode_)}isLeafNode(){return!0}getPriority(){return this.priorityNode_}updatePriority(e){return new B(this.value_,e)}getImmediateChild(e){return e===".priority"?this.priorityNode_:B.__childrenNodeConstructor.EMPTY_NODE}getChild(e){return w(e)?this:v(e)===".priority"?this.priorityNode_:B.__childrenNodeConstructor.EMPTY_NODE}hasChild(){return!1}getPredecessorChildName(e,t){return null}updateImmediateChild(e,t){return e===".priority"?this.updatePriority(t):t.isEmpty()&&e!==".priority"?this:B.__childrenNodeConstructor.EMPTY_NODE.updateImmediateChild(e,t).updatePriority(this.priorityNode_)}updateChild(e,t){const i=v(e);return i===null?t:t.isEmpty()&&i!==".priority"?this:(f(i!==".priority"||we(e)===1,".priority must be the last token in a path"),this.updateImmediateChild(i,B.__childrenNodeConstructor.EMPTY_NODE.updateChild(T(e),t)))}isEmpty(){return!1}numChildren(){return 0}forEachChild(e,t){return!1}val(e){return e&&!this.getPriority().isEmpty()?{".value":this.getValue(),".priority":this.getPriority().val()}:this.getValue()}hash(){if(this.lazyHash_===null){let e="";this.priorityNode_.isEmpty()||(e+="priority:"+Er(this.priorityNode_.val())+":");const t=typeof this.value_;e+=t+":",t==="number"?e+=zs(this.value_):e+=this.value_,this.lazyHash_=$s(e)}return this.lazyHash_}getValue(){return this.value_}compareTo(e){return e===B.__childrenNodeConstructor.EMPTY_NODE?1:e instanceof B.__childrenNodeConstructor?-1:(f(e.isLeafNode(),"Unknown node type"),this.compareToLeafNode_(e))}compareToLeafNode_(e){const t=typeof e.value_,i=typeof this.value_,s=B.VALUE_TYPE_ORDER.indexOf(t),r=B.VALUE_TYPE_ORDER.indexOf(i);return f(s>=0,"Unknown leaf type: "+t),f(r>=0,"Unknown leaf type: "+i),s===r?i==="object"?0:this.value_<e.value_?-1:this.value_===e.value_?0:1:r-s}withIndex(){return this}isIndexed(){return!0}equals(e){if(e===this)return!0;if(e.isLeafNode()){const t=e;return this.value_===t.value_&&this.priorityNode_.equals(t.priorityNode_)}else return!1}}B.VALUE_TYPE_ORDER=["object","boolean","number","string"];/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */let xr,Tr;function qc(n){xr=n}function Kc(n){Tr=n}class Qc extends qt{compare(e,t){const i=e.node.getPriority(),s=t.node.getPriority(),r=i.compareTo(s);return r===0?Le(e.name,t.name):r}isDefinedOn(e){return!e.getPriority().isEmpty()}indexedValueChanged(e,t){return!e.getPriority().equals(t.getPriority())}minPost(){return b.MIN}maxPost(){return new b(de,new B("[PRIORITY-POST]",Tr))}makePost(e,t){const i=xr(e);return new b(t,new B("[PRIORITY-POST]",i))}toString(){return".priority"}}const k=new Qc;/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Yc=Math.log(2);class Jc{constructor(e){const t=r=>parseInt(Math.log(r)/Yc,10),i=r=>parseInt(Array(r+1).join("1"),2);this.count=t(e+1),this.current_=this.count-1;const s=i(this.count);this.bits_=e+1&s}nextBitIsOne(){const e=!(this.bits_&1<<this.current_);return this.current_--,e}}const Yt=function(n,e,t,i){n.sort(e);const s=function(l,c){const d=c-l;let h,u;if(d===0)return null;if(d===1)return h=n[l],u=t?t(h):h,new F(u,h.node,F.BLACK,null,null);{const p=parseInt(d/2,10)+l,_=s(l,p),C=s(p+1,c);return h=n[p],u=t?t(h):h,new F(u,h.node,F.BLACK,_,C)}},r=function(l){let c=null,d=null,h=n.length;const u=function(_,C){const D=h-_,fe=h;h-=_;const ze=s(D+1,fe),ot=n[D],xn=t?t(ot):ot;p(new F(xn,ot.node,C,null,ze))},p=function(_){c?(c.left=_,c=_):(d=_,c=_)};for(let _=0;_<l.count;++_){const C=l.nextBitIsOne(),D=Math.pow(2,l.count-(_+1));C?u(D,F.BLACK):(u(D,F.BLACK),u(D,F.RED))}return d},o=new Jc(n.length),a=r(o);return new z(i||e,a)};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */let ai;const Xe={};class ue{static get Default(){return f(Xe&&k,"ChildrenNode.ts has not been loaded"),ai=ai||new ue({".priority":Xe},{".priority":k}),ai}constructor(e,t){this.indexes_=e,this.indexSet_=t}get(e){const t=Ne(this.indexes_,e);if(!t)throw new Error("No index defined for "+e);return t instanceof z?t:null}hasIndex(e){return X(this.indexSet_,e.toString())}addIndex(e,t){f(e!==re,"KeyIndex always exists and isn't meant to be added to the IndexMap.");const i=[];let s=!1;const r=t.getIterator(b.Wrap);let o=r.getNext();for(;o;)s=s||e.isDefinedOn(o.node),i.push(o),o=r.getNext();let a;s?a=Yt(i,e.getCompare()):a=Xe;const l=e.toString(),c={...this.indexSet_};c[l]=e;const d={...this.indexes_};return d[l]=a,new ue(d,c)}addToIndexes(e,t){const i=Wt(this.indexes_,(s,r)=>{const o=Ne(this.indexSet_,r);if(f(o,"Missing index implementation for "+r),s===Xe)if(o.isDefinedOn(e.node)){const a=[],l=t.getIterator(b.Wrap);let c=l.getNext();for(;c;)c.name!==e.name&&a.push(c),c=l.getNext();return a.push(e),Yt(a,o.getCompare())}else return Xe;else{const a=t.get(e.name);let l=s;return a&&(l=l.remove(new b(e.name,a))),l.insert(e,e.node)}});return new ue(i,this.indexSet_)}removeFromIndexes(e,t){const i=Wt(this.indexes_,s=>{if(s===Xe)return s;{const r=t.get(e.name);return r?s.remove(new b(e.name,r)):s}});return new ue(i,this.indexSet_)}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */let yt;class g{static get EMPTY_NODE(){return yt||(yt=new g(new z(ri),null,ue.Default))}constructor(e,t,i){this.children_=e,this.priorityNode_=t,this.indexMap_=i,this.lazyHash_=null,this.priorityNode_&&Sr(this.priorityNode_),this.children_.isEmpty()&&f(!this.priorityNode_||this.priorityNode_.isEmpty(),"An empty node cannot have a priority")}isLeafNode(){return!1}getPriority(){return this.priorityNode_||yt}updatePriority(e){return this.children_.isEmpty()?this:new g(this.children_,e,this.indexMap_)}getImmediateChild(e){if(e===".priority")return this.getPriority();{const t=this.children_.get(e);return t===null?yt:t}}getChild(e){const t=v(e);return t===null?this:this.getImmediateChild(t).getChild(T(e))}hasChild(e){return this.children_.get(e)!==null}updateImmediateChild(e,t){if(f(t,"We should always be passing snapshot nodes"),e===".priority")return this.updatePriority(t);{const i=new b(e,t);let s,r;t.isEmpty()?(s=this.children_.remove(e),r=this.indexMap_.removeFromIndexes(i,this.children_)):(s=this.children_.insert(e,t),r=this.indexMap_.addToIndexes(i,this.children_));const o=s.isEmpty()?yt:this.priorityNode_;return new g(s,o,r)}}updateChild(e,t){const i=v(e);if(i===null)return t;{f(v(e)!==".priority"||we(e)===1,".priority must be the last token in a path");const s=this.getImmediateChild(i).updateChild(T(e),t);return this.updateImmediateChild(i,s)}}isEmpty(){return this.children_.isEmpty()}numChildren(){return this.children_.count()}val(e){if(this.isEmpty())return null;const t={};let i=0,s=0,r=!0;if(this.forEachChild(k,(o,a)=>{t[o]=a.val(e),i++,r&&g.INTEGER_REGEXP_.test(o)?s=Math.max(s,Number(o)):r=!1}),!e&&r&&s<2*i){const o=[];for(const a in t)o[a]=t[a];return o}else return e&&!this.getPriority().isEmpty()&&(t[".priority"]=this.getPriority().val()),t}hash(){if(this.lazyHash_===null){let e="";this.getPriority().isEmpty()||(e+="priority:"+Er(this.getPriority().val())+":"),this.forEachChild(k,(t,i)=>{const s=i.hash();s!==""&&(e+=":"+t+":"+s)}),this.lazyHash_=e===""?"":$s(e)}return this.lazyHash_}getPredecessorChildName(e,t,i){const s=this.resolveIndex_(i);if(s){const r=s.getPredecessorKey(new b(e,t));return r?r.name:null}else return this.children_.getPredecessorKey(e)}getFirstChildName(e){const t=this.resolveIndex_(e);if(t){const i=t.minKey();return i&&i.name}else return this.children_.minKey()}getFirstChild(e){const t=this.getFirstChildName(e);return t?new b(t,this.children_.get(t)):null}getLastChildName(e){const t=this.resolveIndex_(e);if(t){const i=t.maxKey();return i&&i.name}else return this.children_.maxKey()}getLastChild(e){const t=this.getLastChildName(e);return t?new b(t,this.children_.get(t)):null}forEachChild(e,t){const i=this.resolveIndex_(e);return i?i.inorderTraversal(s=>t(s.name,s.node)):this.children_.inorderTraversal(t)}getIterator(e){return this.getIteratorFrom(e.minPost(),e)}getIteratorFrom(e,t){const i=this.resolveIndex_(t);if(i)return i.getIteratorFrom(e,s=>s);{const s=this.children_.getIteratorFrom(e.name,b.Wrap);let r=s.peek();for(;r!=null&&t.compare(r,e)<0;)s.getNext(),r=s.peek();return s}}getReverseIterator(e){return this.getReverseIteratorFrom(e.maxPost(),e)}getReverseIteratorFrom(e,t){const i=this.resolveIndex_(t);if(i)return i.getReverseIteratorFrom(e,s=>s);{const s=this.children_.getReverseIteratorFrom(e.name,b.Wrap);let r=s.peek();for(;r!=null&&t.compare(r,e)>0;)s.getNext(),r=s.peek();return s}}compareTo(e){return this.isEmpty()?e.isEmpty()?0:-1:e.isLeafNode()||e.isEmpty()?1:e===vt?-1:0}withIndex(e){if(e===re||this.indexMap_.hasIndex(e))return this;{const t=this.indexMap_.addIndex(e,this.children_);return new g(this.children_,this.priorityNode_,t)}}isIndexed(e){return e===re||this.indexMap_.hasIndex(e)}equals(e){if(e===this)return!0;if(e.isLeafNode())return!1;{const t=e;if(this.getPriority().equals(t.getPriority()))if(this.children_.count()===t.children_.count()){const i=this.getIterator(k),s=t.getIterator(k);let r=i.getNext(),o=s.getNext();for(;r&&o;){if(r.name!==o.name||!r.node.equals(o.node))return!1;r=i.getNext(),o=s.getNext()}return r===null&&o===null}else return!1;else return!1}}resolveIndex_(e){return e===re?null:this.indexMap_.get(e.toString())}}g.INTEGER_REGEXP_=/^(0|[1-9]\d*)$/;class Xc extends g{constructor(){super(new z(ri),g.EMPTY_NODE,ue.Default)}compareTo(e){return e===this?0:1}equals(e){return e===this}getPriority(){return this}getImmediateChild(e){return g.EMPTY_NODE}isEmpty(){return!1}}const vt=new Xc;Object.defineProperties(b,{MIN:{value:new b(ye,g.EMPTY_NODE)},MAX:{value:new b(de,vt)}}),Cr.__EMPTY_NODE=g.EMPTY_NODE,B.__childrenNodeConstructor=g,Gc(vt),Kc(vt);/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Zc=!0;function P(n,e=null){if(n===null)return g.EMPTY_NODE;if(typeof n=="object"&&".priority"in n&&(e=n[".priority"]),f(e===null||typeof e=="string"||typeof e=="number"||typeof e=="object"&&".sv"in e,"Invalid priority type found: "+typeof e),typeof n=="object"&&".value"in n&&n[".value"]!==null&&(n=n[".value"]),typeof n!="object"||".sv"in n){const t=n;return new B(t,P(e))}if(!(n instanceof Array)&&Zc){const t=[];let i=!1;if(L(n,(o,a)=>{if(o.substring(0,1)!=="."){const l=P(a);l.isEmpty()||(i=i||!l.getPriority().isEmpty(),t.push(new b(o,l)))}}),t.length===0)return g.EMPTY_NODE;const r=Yt(t,jc,o=>o.name,ri);if(i){const o=Yt(t,k.getCompare());return new g(r,P(e),new ue({".priority":o},{".priority":k}))}else return new g(r,P(e),ue.Default)}else{let t=g.EMPTY_NODE;return L(n,(i,s)=>{if(X(n,i)&&i.substring(0,1)!=="."){const r=P(s);(r.isLeafNode()||!r.isEmpty())&&(t=t.updateImmediateChild(i,r))}}),t.updatePriority(P(e))}}qc(P);/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class li extends qt{constructor(e){super(),this.indexPath_=e,f(!w(e)&&v(e)!==".priority","Can't create PathIndex with empty path or .priority key")}extractChild(e){return e.getChild(this.indexPath_)}isDefinedOn(e){return!e.getChild(this.indexPath_).isEmpty()}compare(e,t){const i=this.extractChild(e.node),s=this.extractChild(t.node),r=i.compareTo(s);return r===0?Le(e.name,t.name):r}makePost(e,t){const i=P(e),s=g.EMPTY_NODE.updateChild(this.indexPath_,i);return new b(t,s)}maxPost(){const e=g.EMPTY_NODE.updateChild(this.indexPath_,vt);return new b(de,e)}toString(){return gt(this.indexPath_,0).join("/")}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class eh extends qt{compare(e,t){const i=e.node.compareTo(t.node);return i===0?Le(e.name,t.name):i}isDefinedOn(e){return!0}indexedValueChanged(e,t){return!e.equals(t)}minPost(){return b.MIN}maxPost(){return b.MAX}makePost(e,t){const i=P(e);return new b(t,i)}toString(){return".value"}}const ci=new eh;/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function kr(n){return{type:"value",snapshotNode:n}}function Ze(n,e){return{type:"child_added",snapshotNode:e,childName:n}}function wt(n,e){return{type:"child_removed",snapshotNode:e,childName:n}}function bt(n,e,t){return{type:"child_changed",snapshotNode:e,childName:n,oldSnap:t}}function th(n,e){return{type:"child_moved",snapshotNode:e,childName:n}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class hi{constructor(e){this.index_=e}updateChild(e,t,i,s,r,o){f(e.isIndexed(this.index_),"A node must be indexed if only a child is updated");const a=e.getImmediateChild(t);return a.getChild(s).equals(i.getChild(s))&&a.isEmpty()===i.isEmpty()||(o!=null&&(i.isEmpty()?e.hasChild(t)?o.trackChildChange(wt(t,a)):f(e.isLeafNode(),"A child remove without an old child only makes sense on a leaf node"):a.isEmpty()?o.trackChildChange(Ze(t,i)):o.trackChildChange(bt(t,i,a))),e.isLeafNode()&&i.isEmpty())?e:e.updateImmediateChild(t,i).withIndex(this.index_)}updateFullNode(e,t,i){return i!=null&&(e.isLeafNode()||e.forEachChild(k,(s,r)=>{t.hasChild(s)||i.trackChildChange(wt(s,r))}),t.isLeafNode()||t.forEachChild(k,(s,r)=>{if(e.hasChild(s)){const o=e.getImmediateChild(s);o.equals(r)||i.trackChildChange(bt(s,r,o))}else i.trackChildChange(Ze(s,r))})),t.withIndex(this.index_)}updatePriority(e,t){return e.isEmpty()?g.EMPTY_NODE:e.updatePriority(t)}filtersNodes(){return!1}getIndexedFilter(){return this}getIndex(){return this.index_}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Ct{constructor(e){this.indexedFilter_=new hi(e.getIndex()),this.index_=e.getIndex(),this.startPost_=Ct.getStartPost_(e),this.endPost_=Ct.getEndPost_(e),this.startIsInclusive_=!e.startAfterSet_,this.endIsInclusive_=!e.endBeforeSet_}getStartPost(){return this.startPost_}getEndPost(){return this.endPost_}matches(e){const t=this.startIsInclusive_?this.index_.compare(this.getStartPost(),e)<=0:this.index_.compare(this.getStartPost(),e)<0,i=this.endIsInclusive_?this.index_.compare(e,this.getEndPost())<=0:this.index_.compare(e,this.getEndPost())<0;return t&&i}updateChild(e,t,i,s,r,o){return this.matches(new b(t,i))||(i=g.EMPTY_NODE),this.indexedFilter_.updateChild(e,t,i,s,r,o)}updateFullNode(e,t,i){t.isLeafNode()&&(t=g.EMPTY_NODE);let s=t.withIndex(this.index_);s=s.updatePriority(g.EMPTY_NODE);const r=this;return t.forEachChild(k,(o,a)=>{r.matches(new b(o,a))||(s=s.updateImmediateChild(o,g.EMPTY_NODE))}),this.indexedFilter_.updateFullNode(e,s,i)}updatePriority(e,t){return e}filtersNodes(){return!0}getIndexedFilter(){return this.indexedFilter_}getIndex(){return this.index_}static getStartPost_(e){if(e.hasStart()){const t=e.getIndexStartName();return e.getIndex().makePost(e.getIndexStartValue(),t)}else return e.getIndex().minPost()}static getEndPost_(e){if(e.hasEnd()){const t=e.getIndexEndName();return e.getIndex().makePost(e.getIndexEndValue(),t)}else return e.getIndex().maxPost()}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class nh{constructor(e){this.withinDirectionalStart=t=>this.reverse_?this.withinEndPost(t):this.withinStartPost(t),this.withinDirectionalEnd=t=>this.reverse_?this.withinStartPost(t):this.withinEndPost(t),this.withinStartPost=t=>{const i=this.index_.compare(this.rangedFilter_.getStartPost(),t);return this.startIsInclusive_?i<=0:i<0},this.withinEndPost=t=>{const i=this.index_.compare(t,this.rangedFilter_.getEndPost());return this.endIsInclusive_?i<=0:i<0},this.rangedFilter_=new Ct(e),this.index_=e.getIndex(),this.limit_=e.getLimit(),this.reverse_=!e.isViewFromLeft(),this.startIsInclusive_=!e.startAfterSet_,this.endIsInclusive_=!e.endBeforeSet_}updateChild(e,t,i,s,r,o){return this.rangedFilter_.matches(new b(t,i))||(i=g.EMPTY_NODE),e.getImmediateChild(t).equals(i)?e:e.numChildren()<this.limit_?this.rangedFilter_.getIndexedFilter().updateChild(e,t,i,s,r,o):this.fullLimitUpdateChild_(e,t,i,r,o)}updateFullNode(e,t,i){let s;if(t.isLeafNode()||t.isEmpty())s=g.EMPTY_NODE.withIndex(this.index_);else if(this.limit_*2<t.numChildren()&&t.isIndexed(this.index_)){s=g.EMPTY_NODE.withIndex(this.index_);let r;this.reverse_?r=t.getReverseIteratorFrom(this.rangedFilter_.getEndPost(),this.index_):r=t.getIteratorFrom(this.rangedFilter_.getStartPost(),this.index_);let o=0;for(;r.hasNext()&&o<this.limit_;){const a=r.getNext();if(this.withinDirectionalStart(a))if(this.withinDirectionalEnd(a))s=s.updateImmediateChild(a.name,a.node),o++;else break;else continue}}else{s=t.withIndex(this.index_),s=s.updatePriority(g.EMPTY_NODE);let r;this.reverse_?r=s.getReverseIterator(this.index_):r=s.getIterator(this.index_);let o=0;for(;r.hasNext();){const a=r.getNext();o<this.limit_&&this.withinDirectionalStart(a)&&this.withinDirectionalEnd(a)?o++:s=s.updateImmediateChild(a.name,g.EMPTY_NODE)}}return this.rangedFilter_.getIndexedFilter().updateFullNode(e,s,i)}updatePriority(e,t){return e}filtersNodes(){return!0}getIndexedFilter(){return this.rangedFilter_.getIndexedFilter()}getIndex(){return this.index_}fullLimitUpdateChild_(e,t,i,s,r){let o;if(this.reverse_){const h=this.index_.getCompare();o=(u,p)=>h(p,u)}else o=this.index_.getCompare();const a=e;f(a.numChildren()===this.limit_,"");const l=new b(t,i),c=this.reverse_?a.getFirstChild(this.index_):a.getLastChild(this.index_),d=this.rangedFilter_.matches(l);if(a.hasChild(t)){const h=a.getImmediateChild(t);let u=s.getChildAfterChild(this.index_,c,this.reverse_);for(;u!=null&&(u.name===t||a.hasChild(u.name));)u=s.getChildAfterChild(this.index_,u,this.reverse_);const p=u==null?1:o(u,l);if(d&&!i.isEmpty()&&p>=0)return r!=null&&r.trackChildChange(bt(t,i,h)),a.updateImmediateChild(t,i);{r!=null&&r.trackChildChange(wt(t,h));const C=a.updateImmediateChild(t,g.EMPTY_NODE);return u!=null&&this.rangedFilter_.matches(u)?(r!=null&&r.trackChildChange(Ze(u.name,u.node)),C.updateImmediateChild(u.name,u.node)):C}}else return i.isEmpty()?e:d&&o(c,l)>=0?(r!=null&&(r.trackChildChange(wt(c.name,c.node)),r.trackChildChange(Ze(t,i))),a.updateImmediateChild(t,i).updateImmediateChild(c.name,g.EMPTY_NODE)):e}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Jt{constructor(){this.limitSet_=!1,this.startSet_=!1,this.startNameSet_=!1,this.startAfterSet_=!1,this.endSet_=!1,this.endNameSet_=!1,this.endBeforeSet_=!1,this.limit_=0,this.viewFrom_="",this.indexStartValue_=null,this.indexStartName_="",this.indexEndValue_=null,this.indexEndName_="",this.index_=k}hasStart(){return this.startSet_}isViewFromLeft(){return this.viewFrom_===""?this.startSet_:this.viewFrom_==="l"}getIndexStartValue(){return f(this.startSet_,"Only valid if start has been set"),this.indexStartValue_}getIndexStartName(){return f(this.startSet_,"Only valid if start has been set"),this.startNameSet_?this.indexStartName_:ye}hasEnd(){return this.endSet_}getIndexEndValue(){return f(this.endSet_,"Only valid if end has been set"),this.indexEndValue_}getIndexEndName(){return f(this.endSet_,"Only valid if end has been set"),this.endNameSet_?this.indexEndName_:de}hasLimit(){return this.limitSet_}hasAnchoredLimit(){return this.limitSet_&&this.viewFrom_!==""}getLimit(){return f(this.limitSet_,"Only valid if limit has been set"),this.limit_}getIndex(){return this.index_}loadsAllData(){return!(this.startSet_||this.endSet_||this.limitSet_)}isDefault(){return this.loadsAllData()&&this.index_===k}copy(){const e=new Jt;return e.limitSet_=this.limitSet_,e.limit_=this.limit_,e.startSet_=this.startSet_,e.startAfterSet_=this.startAfterSet_,e.indexStartValue_=this.indexStartValue_,e.startNameSet_=this.startNameSet_,e.indexStartName_=this.indexStartName_,e.endSet_=this.endSet_,e.endBeforeSet_=this.endBeforeSet_,e.indexEndValue_=this.indexEndValue_,e.endNameSet_=this.endNameSet_,e.indexEndName_=this.indexEndName_,e.index_=this.index_,e.viewFrom_=this.viewFrom_,e}}function ih(n){return n.loadsAllData()?new hi(n.getIndex()):n.hasLimit()?new nh(n):new Ct(n)}function sh(n,e){const t=n.copy();return t.limitSet_=!0,t.limit_=e,t.viewFrom_="l",t}function rh(n,e){const t=n.copy();return t.limitSet_=!0,t.limit_=e,t.viewFrom_="r",t}function di(n,e,t){const i=n.copy();return i.startSet_=!0,e===void 0&&(e=null),i.indexStartValue_=e,t!=null?(i.startNameSet_=!0,i.indexStartName_=t):(i.startNameSet_=!1,i.indexStartName_=""),i}function oh(n,e,t){let i;return n.index_===re||t?i=di(n,e,t):i=di(n,e,de),i.startAfterSet_=!0,i}function ui(n,e,t){const i=n.copy();return i.endSet_=!0,e===void 0&&(e=null),i.indexEndValue_=e,t!==void 0?(i.endNameSet_=!0,i.indexEndName_=t):(i.endNameSet_=!1,i.indexEndName_=""),i}function ah(n,e,t){let i;return n.index_===re||t?i=ui(n,e,t):i=ui(n,e,ye),i.endBeforeSet_=!0,i}function Xt(n,e){const t=n.copy();return t.index_=e,t}function Ar(n){const e={};if(n.isDefault())return e;let t;if(n.index_===k?t="$priority":n.index_===ci?t="$value":n.index_===re?t="$key":(f(n.index_ instanceof li,"Unrecognized index type!"),t=n.index_.toString()),e.orderBy=O(t),n.startSet_){const i=n.startAfterSet_?"startAfter":"startAt";e[i]=O(n.indexStartValue_),n.startNameSet_&&(e[i]+=","+O(n.indexStartName_))}if(n.endSet_){const i=n.endBeforeSet_?"endBefore":"endAt";e[i]=O(n.indexEndValue_),n.endNameSet_&&(e[i]+=","+O(n.indexEndName_))}return n.limitSet_&&(n.isViewFromLeft()?e.limitToFirst=n.limit_:e.limitToLast=n.limit_),e}function Nr(n){const e={};if(n.startSet_&&(e.sp=n.indexStartValue_,n.startNameSet_&&(e.sn=n.indexStartName_),e.sin=!n.startAfterSet_),n.endSet_&&(e.ep=n.indexEndValue_,n.endNameSet_&&(e.en=n.indexEndName_),e.ein=!n.endBeforeSet_),n.limitSet_){e.l=n.limit_;let t=n.viewFrom_;t===""&&(n.isViewFromLeft()?t="l":t="r"),e.vf=t}return n.index_!==k&&(e.i=n.index_.toString()),e}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Zt extends pr{reportStats(e){throw new Error("Method not implemented.")}static getListenId_(e,t){return t!==void 0?"tag$"+t:(f(e._queryParams.isDefault(),"should have a tag if it's not a default query."),e._path.toString())}constructor(e,t,i,s){super(),this.repoInfo_=e,this.onDataUpdate_=t,this.authTokenProvider_=i,this.appCheckTokenProvider_=s,this.log_=ft("p:rest:"),this.listens_={}}listen(e,t,i,s){const r=e._path.toString();this.log_("Listen called for "+r+" "+e._queryIdentifier);const o=Zt.getListenId_(e,i),a={};this.listens_[o]=a;const l=Ar(e._queryParams);this.restRequest_(r+".json",l,(c,d)=>{let h=d;if(c===404&&(h=null,c=null),c===null&&this.onDataUpdate_(r,h,!1,i),Ne(this.listens_,o)===a){let u;c?c===401?u="permission_denied":u="rest_error:"+c:u="ok",s(u,null)}})}unlisten(e,t){const i=Zt.getListenId_(e,t);delete this.listens_[i]}get(e){const t=Ar(e._queryParams),i=e._path.toString(),s=new j;return this.restRequest_(i+".json",t,(r,o)=>{let a=o;r===404&&(a=null,r=null),r===null?(this.onDataUpdate_(i,a,!1,null),s.resolve(a)):s.reject(new Error(a))}),s.promise}refreshAuthToken(e){}restRequest_(e,t={},i){return t.format="export",Promise.all([this.authTokenProvider_.getToken(!1),this.appCheckTokenProvider_.getToken(!1)]).then(([s,r])=>{s&&s.accessToken&&(t.auth=s.accessToken),r&&r.token&&(t.ac=r.token);const o=(this.repoInfo_.secure?"https://":"http://")+this.repoInfo_.host+e+"?ns="+this.repoInfo_.namespace+Na(t);this.log_("Sending REST request for "+o);const a=new XMLHttpRequest;a.onreadystatechange=()=>{if(i&&a.readyState===4){this.log_("REST Response for "+o+" received. status:",a.status,"response:",a.responseText);let l=null;if(a.status>=200&&a.status<300){try{l=lt(a.responseText)}catch{$("Failed to parse JSON response for "+o+": "+a.responseText)}i(null,l)}else a.status!==401&&a.status!==404&&$("Got unsuccessful REST response for "+o+" Status: "+a.status),i(a.status);i=null}},a.open("GET",o,!0),a.send()})}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class lh{constructor(){this.rootNode_=g.EMPTY_NODE}getNode(e){return this.rootNode_.getChild(e)}updateSnapshot(e,t){this.rootNode_=this.rootNode_.updateChild(e,t)}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function en(){return{value:null,children:new Map}}function et(n,e,t){if(w(e))n.value=t,n.children.clear();else if(n.value!==null)n.value=n.value.updateChild(e,t);else{const i=v(e);n.children.has(i)||n.children.set(i,en());const s=n.children.get(i);e=T(e),et(s,e,t)}}function fi(n,e){if(w(e))return n.value=null,n.children.clear(),!0;if(n.value!==null){if(n.value.isLeafNode())return!1;{const t=n.value;return n.value=null,t.forEachChild(k,(i,s)=>{et(n,new S(i),s)}),fi(n,e)}}else if(n.children.size>0){const t=v(e);return e=T(e),n.children.has(t)&&fi(n.children.get(t),e)&&n.children.delete(t),n.children.size===0}else return!0}function pi(n,e,t){n.value!==null?t(e,n.value):ch(n,(i,s)=>{const r=new S(e.toString()+"/"+i);pi(s,r,t)})}function ch(n,e){n.children.forEach((t,i)=>{e(i,t)})}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class hh{constructor(e){this.collection_=e,this.last_=null}get(){const e=this.collection_.get(),t={...e};return this.last_&&L(this.last_,(i,s)=>{t[i]=t[i]-s}),this.last_=e,t}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Rr=10*1e3,dh=30*1e3,uh=300*1e3;class fh{constructor(e,t){this.server_=t,this.statsToReport_={},this.statsListener_=new hh(e);const i=Rr+(dh-Rr)*Math.random();_t(this.reportStats_.bind(this),Math.floor(i))}reportStats_(){const e=this.statsListener_.get(),t={};let i=!1;L(e,(s,r)=>{r>0&&X(this.statsToReport_,s)&&(t[s]=r,i=!0)}),i&&this.server_.reportStats(t),_t(this.reportStats_.bind(this),Math.floor(Math.random()*2*uh))}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */var Z;(function(n){n[n.OVERWRITE=0]="OVERWRITE",n[n.MERGE=1]="MERGE",n[n.ACK_USER_WRITE=2]="ACK_USER_WRITE",n[n.LISTEN_COMPLETE=3]="LISTEN_COMPLETE"})(Z||(Z={}));function _i(){return{fromUser:!0,fromServer:!1,queryId:null,tagged:!1}}function gi(){return{fromUser:!1,fromServer:!0,queryId:null,tagged:!1}}function mi(n){return{fromUser:!1,fromServer:!0,queryId:n,tagged:!0}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class tn{constructor(e,t,i){this.path=e,this.affectedTree=t,this.revert=i,this.type=Z.ACK_USER_WRITE,this.source=_i()}operationForChild(e){if(w(this.path)){if(this.affectedTree.value!=null)return f(this.affectedTree.children.isEmpty(),"affectedTree should not have overlapping affected paths."),this;{const t=this.affectedTree.subtree(new S(e));return new tn(E(),t,this.revert)}}else return f(v(this.path)===e,"operationForChild called for unrelated child."),new tn(T(this.path),this.affectedTree,this.revert)}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Et{constructor(e,t){this.source=e,this.path=t,this.type=Z.LISTEN_COMPLETE}operationForChild(e){return w(this.path)?new Et(this.source,E()):new Et(this.source,T(this.path))}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Be{constructor(e,t,i){this.source=e,this.path=t,this.snap=i,this.type=Z.OVERWRITE}operationForChild(e){return w(this.path)?new Be(this.source,E(),this.snap.getImmediateChild(e)):new Be(this.source,T(this.path),this.snap)}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class tt{constructor(e,t,i){this.source=e,this.path=t,this.children=i,this.type=Z.MERGE}operationForChild(e){if(w(this.path)){const t=this.children.subtree(new S(e));return t.isEmpty()?null:t.value?new Be(this.source,E(),t.value):new tt(this.source,E(),t)}else return f(v(this.path)===e,"Can't get a merge for a child not on the path of the operation"),new tt(this.source,T(this.path),this.children)}toString(){return"Operation("+this.path+": "+this.source.toString()+" merge: "+this.children.toString()+")"}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class be{constructor(e,t,i){this.node_=e,this.fullyInitialized_=t,this.filtered_=i}isFullyInitialized(){return this.fullyInitialized_}isFiltered(){return this.filtered_}isCompleteForPath(e){if(w(e))return this.isFullyInitialized()&&!this.filtered_;const t=v(e);return this.isCompleteForChild(t)}isCompleteForChild(e){return this.isFullyInitialized()&&!this.filtered_||this.node_.hasChild(e)}getNode(){return this.node_}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class ph{constructor(e){this.query_=e,this.index_=this.query_._queryParams.getIndex()}}function _h(n,e,t,i){const s=[],r=[];return e.forEach(o=>{o.type==="child_changed"&&n.index_.indexedValueChanged(o.oldSnap,o.snapshotNode)&&r.push(th(o.childName,o.snapshotNode))}),St(n,s,"child_removed",e,i,t),St(n,s,"child_added",e,i,t),St(n,s,"child_moved",r,i,t),St(n,s,"child_changed",e,i,t),St(n,s,"value",e,i,t),s}function St(n,e,t,i,s,r){const o=i.filter(a=>a.type===t);o.sort((a,l)=>mh(n,a,l)),o.forEach(a=>{const l=gh(n,a,r);s.forEach(c=>{c.respondsTo(a.type)&&e.push(c.createEvent(l,n.query_))})})}function gh(n,e,t){return e.type==="value"||e.type==="child_removed"||(e.prevName=t.getPredecessorChildName(e.childName,e.snapshotNode,n.index_)),e}function mh(n,e,t){if(e.childName==null||t.childName==null)throw Ve("Should only compare child_ events.");const i=new b(e.childName,e.snapshotNode),s=new b(t.childName,t.snapshotNode);return n.index_.compare(i,s)}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function nn(n,e){return{eventCache:n,serverCache:e}}function It(n,e,t,i){return nn(new be(e,t,i),n.serverCache)}function Pr(n,e,t,i){return nn(n.eventCache,new be(e,t,i))}function sn(n){return n.eventCache.isFullyInitialized()?n.eventCache.getNode():null}function $e(n){return n.serverCache.isFullyInitialized()?n.serverCache.getNode():null}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */let yi;const yh=()=>(yi||(yi=new z(ec)),yi);class A{static fromObject(e){let t=new A(null);return L(e,(i,s)=>{t=t.set(new S(i),s)}),t}constructor(e,t=yh()){this.value=e,this.children=t}isEmpty(){return this.value===null&&this.children.isEmpty()}findRootMostMatchingPathAndValue(e,t){if(this.value!=null&&t(this.value))return{path:E(),value:this.value};if(w(e))return null;{const i=v(e),s=this.children.get(i);if(s!==null){const r=s.findRootMostMatchingPathAndValue(T(e),t);return r!=null?{path:R(new S(i),r.path),value:r.value}:null}else return null}}findRootMostValueAndPath(e){return this.findRootMostMatchingPathAndValue(e,()=>!0)}subtree(e){if(w(e))return this;{const t=v(e),i=this.children.get(t);return i!==null?i.subtree(T(e)):new A(null)}}set(e,t){if(w(e))return new A(t,this.children);{const i=v(e),r=(this.children.get(i)||new A(null)).set(T(e),t),o=this.children.insert(i,r);return new A(this.value,o)}}remove(e){if(w(e))return this.children.isEmpty()?new A(null):new A(null,this.children);{const t=v(e),i=this.children.get(t);if(i){const s=i.remove(T(e));let r;return s.isEmpty()?r=this.children.remove(t):r=this.children.insert(t,s),this.value===null&&r.isEmpty()?new A(null):new A(this.value,r)}else return this}}get(e){if(w(e))return this.value;{const t=v(e),i=this.children.get(t);return i?i.get(T(e)):null}}setTree(e,t){if(w(e))return t;{const i=v(e),r=(this.children.get(i)||new A(null)).setTree(T(e),t);let o;return r.isEmpty()?o=this.children.remove(i):o=this.children.insert(i,r),new A(this.value,o)}}fold(e){return this.fold_(E(),e)}fold_(e,t){const i={};return this.children.inorderTraversal((s,r)=>{i[s]=r.fold_(R(e,s),t)}),t(e,this.value,i)}findOnPath(e,t){return this.findOnPath_(e,E(),t)}findOnPath_(e,t,i){const s=this.value?i(t,this.value):!1;if(s)return s;if(w(e))return null;{const r=v(e),o=this.children.get(r);return o?o.findOnPath_(T(e),R(t,r),i):null}}foreachOnPath(e,t){return this.foreachOnPath_(e,E(),t)}foreachOnPath_(e,t,i){if(w(e))return this;{this.value&&i(t,this.value);const s=v(e),r=this.children.get(s);return r?r.foreachOnPath_(T(e),R(t,s),i):new A(null)}}foreach(e){this.foreach_(E(),e)}foreach_(e,t){this.children.inorderTraversal((i,s)=>{s.foreach_(R(e,i),t)}),this.value&&t(e,this.value)}foreachChild(e){this.children.inorderTraversal((t,i)=>{i.value&&e(t,i.value)})}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class ee{constructor(e){this.writeTree_=e}static empty(){return new ee(new A(null))}}function xt(n,e,t){if(w(e))return new ee(new A(t));{const i=n.writeTree_.findRootMostValueAndPath(e);if(i!=null){const s=i.path;let r=i.value;const o=W(s,e);return r=r.updateChild(o,t),new ee(n.writeTree_.set(s,r))}else{const s=new A(t),r=n.writeTree_.setTree(e,s);return new ee(r)}}}function vi(n,e,t){let i=n;return L(t,(s,r)=>{i=xt(i,R(e,s),r)}),i}function Dr(n,e){if(w(e))return ee.empty();{const t=n.writeTree_.setTree(e,new A(null));return new ee(t)}}function wi(n,e){return We(n,e)!=null}function We(n,e){const t=n.writeTree_.findRootMostValueAndPath(e);return t!=null?n.writeTree_.get(t.path).getChild(W(t.path,e)):null}function Or(n){const e=[],t=n.writeTree_.value;return t!=null?t.isLeafNode()||t.forEachChild(k,(i,s)=>{e.push(new b(i,s))}):n.writeTree_.children.inorderTraversal((i,s)=>{s.value!=null&&e.push(new b(i,s.value))}),e}function Ce(n,e){if(w(e))return n;{const t=We(n,e);return t!=null?new ee(new A(t)):new ee(n.writeTree_.subtree(e))}}function bi(n){return n.writeTree_.isEmpty()}function nt(n,e){return Mr(E(),n.writeTree_,e)}function Mr(n,e,t){if(e.value!=null)return t.updateChild(n,e.value);{let i=null;return e.children.inorderTraversal((s,r)=>{s===".priority"?(f(r.value!==null,"Priority writes must always be leaf nodes"),i=r.value):t=Mr(R(n,s),r,t)}),!t.getChild(n).isEmpty()&&i!==null&&(t=t.updateChild(R(n,".priority"),i)),t}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function rn(n,e){return Wr(e,n)}function vh(n,e,t,i,s){f(i>n.lastWriteId,"Stacking an older write on top of newer ones"),s===void 0&&(s=!0),n.allWrites.push({path:e,snap:t,writeId:i,visible:s}),s&&(n.visibleWrites=xt(n.visibleWrites,e,t)),n.lastWriteId=i}function wh(n,e,t,i){f(i>n.lastWriteId,"Stacking an older merge on top of newer ones"),n.allWrites.push({path:e,children:t,writeId:i,visible:!0}),n.visibleWrites=vi(n.visibleWrites,e,t),n.lastWriteId=i}function bh(n,e){for(let t=0;t<n.allWrites.length;t++){const i=n.allWrites[t];if(i.writeId===e)return i}return null}function Ch(n,e){const t=n.allWrites.findIndex(a=>a.writeId===e);f(t>=0,"removeWrite called with nonexistent writeId.");const i=n.allWrites[t];n.allWrites.splice(t,1);let s=i.visible,r=!1,o=n.allWrites.length-1;for(;s&&o>=0;){const a=n.allWrites[o];a.visible&&(o>=t&&Eh(a,i.path)?s=!1:K(i.path,a.path)&&(r=!0)),o--}if(s){if(r)return Sh(n),!0;if(i.snap)n.visibleWrites=Dr(n.visibleWrites,i.path);else{const a=i.children;L(a,l=>{n.visibleWrites=Dr(n.visibleWrites,R(i.path,l))})}return!0}else return!1}function Eh(n,e){if(n.snap)return K(n.path,e);for(const t in n.children)if(n.children.hasOwnProperty(t)&&K(R(n.path,t),e))return!0;return!1}function Sh(n){n.visibleWrites=Lr(n.allWrites,Ih,E()),n.allWrites.length>0?n.lastWriteId=n.allWrites[n.allWrites.length-1].writeId:n.lastWriteId=-1}function Ih(n){return n.visible}function Lr(n,e,t){let i=ee.empty();for(let s=0;s<n.length;++s){const r=n[s];if(e(r)){const o=r.path;let a;if(r.snap)K(t,o)?(a=W(t,o),i=xt(i,a,r.snap)):K(o,t)&&(a=W(o,t),i=xt(i,E(),r.snap.getChild(a)));else if(r.children){if(K(t,o))a=W(t,o),i=vi(i,a,r.children);else if(K(o,t))if(a=W(o,t),w(a))i=vi(i,E(),r.children);else{const l=Ne(r.children,v(a));if(l){const c=l.getChild(T(a));i=xt(i,E(),c)}}}else throw Ve("WriteRecord should have .snap or .children")}}return i}function Fr(n,e,t,i,s){if(!i&&!s){const r=We(n.visibleWrites,e);if(r!=null)return r;{const o=Ce(n.visibleWrites,e);if(bi(o))return t;if(t==null&&!wi(o,E()))return null;{const a=t||g.EMPTY_NODE;return nt(o,a)}}}else{const r=Ce(n.visibleWrites,e);if(!s&&bi(r))return t;if(!s&&t==null&&!wi(r,E()))return null;{const o=function(c){return(c.visible||s)&&(!i||!~i.indexOf(c.writeId))&&(K(c.path,e)||K(e,c.path))},a=Lr(n.allWrites,o,e),l=t||g.EMPTY_NODE;return nt(a,l)}}}function xh(n,e,t){let i=g.EMPTY_NODE;const s=We(n.visibleWrites,e);if(s)return s.isLeafNode()||s.forEachChild(k,(r,o)=>{i=i.updateImmediateChild(r,o)}),i;if(t){const r=Ce(n.visibleWrites,e);return t.forEachChild(k,(o,a)=>{const l=nt(Ce(r,new S(o)),a);i=i.updateImmediateChild(o,l)}),Or(r).forEach(o=>{i=i.updateImmediateChild(o.name,o.node)}),i}else{const r=Ce(n.visibleWrites,e);return Or(r).forEach(o=>{i=i.updateImmediateChild(o.name,o.node)}),i}}function Th(n,e,t,i,s){f(i||s,"Either existingEventSnap or existingServerSnap must exist");const r=R(e,t);if(wi(n.visibleWrites,r))return null;{const o=Ce(n.visibleWrites,r);return bi(o)?s.getChild(t):nt(o,s.getChild(t))}}function kh(n,e,t,i){const s=R(e,t),r=We(n.visibleWrites,s);if(r!=null)return r;if(i.isCompleteForChild(t)){const o=Ce(n.visibleWrites,s);return nt(o,i.getNode().getImmediateChild(t))}else return null}function Ah(n,e){return We(n.visibleWrites,e)}function Nh(n,e,t,i,s,r,o){let a;const l=Ce(n.visibleWrites,e),c=We(l,E());if(c!=null)a=c;else if(t!=null)a=nt(l,t);else return[];if(a=a.withIndex(o),!a.isEmpty()&&!a.isLeafNode()){const d=[],h=o.getCompare(),u=r?a.getReverseIteratorFrom(i,o):a.getIteratorFrom(i,o);let p=u.getNext();for(;p&&d.length<s;)h(p,i)!==0&&d.push(p),p=u.getNext();return d}else return[]}function Rh(){return{visibleWrites:ee.empty(),allWrites:[],lastWriteId:-1}}function on(n,e,t,i){return Fr(n.writeTree,n.treePath,e,t,i)}function Ci(n,e){return xh(n.writeTree,n.treePath,e)}function Br(n,e,t,i){return Th(n.writeTree,n.treePath,e,t,i)}function an(n,e){return Ah(n.writeTree,R(n.treePath,e))}function Ph(n,e,t,i,s,r){return Nh(n.writeTree,n.treePath,e,t,i,s,r)}function Ei(n,e,t){return kh(n.writeTree,n.treePath,e,t)}function $r(n,e){return Wr(R(n.treePath,e),n.writeTree)}function Wr(n,e){return{treePath:n,writeTree:e}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Dh{constructor(){this.changeMap=new Map}trackChildChange(e){const t=e.type,i=e.childName;f(t==="child_added"||t==="child_changed"||t==="child_removed","Only child changes supported for tracking"),f(i!==".priority","Only non-priority child changes can be tracked.");const s=this.changeMap.get(i);if(s){const r=s.type;if(t==="child_added"&&r==="child_removed")this.changeMap.set(i,bt(i,e.snapshotNode,s.snapshotNode));else if(t==="child_removed"&&r==="child_added")this.changeMap.delete(i);else if(t==="child_removed"&&r==="child_changed")this.changeMap.set(i,wt(i,s.oldSnap));else if(t==="child_changed"&&r==="child_added")this.changeMap.set(i,Ze(i,e.snapshotNode));else if(t==="child_changed"&&r==="child_changed")this.changeMap.set(i,bt(i,e.snapshotNode,s.oldSnap));else throw Ve("Illegal combination of changes: "+e+" occurred after "+s)}else this.changeMap.set(i,e)}getChanges(){return Array.from(this.changeMap.values())}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Oh{getCompleteChild(e){return null}getChildAfterChild(e,t,i){return null}}const Ur=new Oh;class Si{constructor(e,t,i=null){this.writes_=e,this.viewCache_=t,this.optCompleteServerCache_=i}getCompleteChild(e){const t=this.viewCache_.eventCache;if(t.isCompleteForChild(e))return t.getNode().getImmediateChild(e);{const i=this.optCompleteServerCache_!=null?new be(this.optCompleteServerCache_,!0,!1):this.viewCache_.serverCache;return Ei(this.writes_,e,i)}}getChildAfterChild(e,t,i){const s=this.optCompleteServerCache_!=null?this.optCompleteServerCache_:$e(this.viewCache_),r=Ph(this.writes_,s,t,1,i,e);return r.length===0?null:r[0]}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Mh(n){return{filter:n}}function Lh(n,e){f(e.eventCache.getNode().isIndexed(n.filter.getIndex()),"Event snap not indexed"),f(e.serverCache.getNode().isIndexed(n.filter.getIndex()),"Server snap not indexed")}function Fh(n,e,t,i,s){const r=new Dh;let o,a;if(t.type===Z.OVERWRITE){const c=t;c.source.fromUser?o=Ii(n,e,c.path,c.snap,i,s,r):(f(c.source.fromServer,"Unknown source."),a=c.source.tagged||e.serverCache.isFiltered()&&!w(c.path),o=ln(n,e,c.path,c.snap,i,s,a,r))}else if(t.type===Z.MERGE){const c=t;c.source.fromUser?o=$h(n,e,c.path,c.children,i,s,r):(f(c.source.fromServer,"Unknown source."),a=c.source.tagged||e.serverCache.isFiltered(),o=xi(n,e,c.path,c.children,i,s,a,r))}else if(t.type===Z.ACK_USER_WRITE){const c=t;c.revert?o=Hh(n,e,c.path,i,s,r):o=Wh(n,e,c.path,c.affectedTree,i,s,r)}else if(t.type===Z.LISTEN_COMPLETE)o=Uh(n,e,t.path,i,r);else throw Ve("Unknown operation type: "+t.type);const l=r.getChanges();return Bh(e,o,l),{viewCache:o,changes:l}}function Bh(n,e,t){const i=e.eventCache;if(i.isFullyInitialized()){const s=i.getNode().isLeafNode()||i.getNode().isEmpty(),r=sn(n);(t.length>0||!n.eventCache.isFullyInitialized()||s&&!i.getNode().equals(r)||!i.getNode().getPriority().equals(r.getPriority()))&&t.push(kr(sn(e)))}}function Hr(n,e,t,i,s,r){const o=e.eventCache;if(an(i,t)!=null)return e;{let a,l;if(w(t))if(f(e.serverCache.isFullyInitialized(),"If change path is empty, we must have complete server data"),e.serverCache.isFiltered()){const c=$e(e),d=c instanceof g?c:g.EMPTY_NODE,h=Ci(i,d);a=n.filter.updateFullNode(e.eventCache.getNode(),h,r)}else{const c=on(i,$e(e));a=n.filter.updateFullNode(e.eventCache.getNode(),c,r)}else{const c=v(t);if(c===".priority"){f(we(t)===1,"Can't have a priority with additional path components");const d=o.getNode();l=e.serverCache.getNode();const h=Br(i,t,d,l);h!=null?a=n.filter.updatePriority(d,h):a=o.getNode()}else{const d=T(t);let h;if(o.isCompleteForChild(c)){l=e.serverCache.getNode();const u=Br(i,t,o.getNode(),l);u!=null?h=o.getNode().getImmediateChild(c).updateChild(d,u):h=o.getNode().getImmediateChild(c)}else h=Ei(i,c,e.serverCache);h!=null?a=n.filter.updateChild(o.getNode(),c,h,d,s,r):a=o.getNode()}}return It(e,a,o.isFullyInitialized()||w(t),n.filter.filtersNodes())}}function ln(n,e,t,i,s,r,o,a){const l=e.serverCache;let c;const d=o?n.filter:n.filter.getIndexedFilter();if(w(t))c=d.updateFullNode(l.getNode(),i,null);else if(d.filtersNodes()&&!l.isFiltered()){const p=l.getNode().updateChild(t,i);c=d.updateFullNode(l.getNode(),p,null)}else{const p=v(t);if(!l.isCompleteForPath(t)&&we(t)>1)return e;const _=T(t),D=l.getNode().getImmediateChild(p).updateChild(_,i);p===".priority"?c=d.updatePriority(l.getNode(),D):c=d.updateChild(l.getNode(),p,D,_,Ur,null)}const h=Pr(e,c,l.isFullyInitialized()||w(t),d.filtersNodes()),u=new Si(s,h,r);return Hr(n,h,t,s,u,a)}function Ii(n,e,t,i,s,r,o){const a=e.eventCache;let l,c;const d=new Si(s,e,r);if(w(t))c=n.filter.updateFullNode(e.eventCache.getNode(),i,o),l=It(e,c,!0,n.filter.filtersNodes());else{const h=v(t);if(h===".priority")c=n.filter.updatePriority(e.eventCache.getNode(),i),l=It(e,c,a.isFullyInitialized(),a.isFiltered());else{const u=T(t),p=a.getNode().getImmediateChild(h);let _;if(w(u))_=i;else{const C=d.getCompleteChild(h);C!=null?ni(u)===".priority"&&C.getChild(yr(u)).isEmpty()?_=C:_=C.updateChild(u,i):_=g.EMPTY_NODE}if(p.equals(_))l=e;else{const C=n.filter.updateChild(a.getNode(),h,_,u,d,o);l=It(e,C,a.isFullyInitialized(),n.filter.filtersNodes())}}}return l}function zr(n,e){return n.eventCache.isCompleteForChild(e)}function $h(n,e,t,i,s,r,o){let a=e;return i.foreach((l,c)=>{const d=R(t,l);zr(e,v(d))&&(a=Ii(n,a,d,c,s,r,o))}),i.foreach((l,c)=>{const d=R(t,l);zr(e,v(d))||(a=Ii(n,a,d,c,s,r,o))}),a}function Vr(n,e,t){return t.foreach((i,s)=>{e=e.updateChild(i,s)}),e}function xi(n,e,t,i,s,r,o,a){if(e.serverCache.getNode().isEmpty()&&!e.serverCache.isFullyInitialized())return e;let l=e,c;w(t)?c=i:c=new A(null).setTree(t,i);const d=e.serverCache.getNode();return c.children.inorderTraversal((h,u)=>{if(d.hasChild(h)){const p=e.serverCache.getNode().getImmediateChild(h),_=Vr(n,p,u);l=ln(n,l,new S(h),_,s,r,o,a)}}),c.children.inorderTraversal((h,u)=>{const p=!e.serverCache.isCompleteForChild(h)&&u.value===null;if(!d.hasChild(h)&&!p){const _=e.serverCache.getNode().getImmediateChild(h),C=Vr(n,_,u);l=ln(n,l,new S(h),C,s,r,o,a)}}),l}function Wh(n,e,t,i,s,r,o){if(an(s,t)!=null)return e;const a=e.serverCache.isFiltered(),l=e.serverCache;if(i.value!=null){if(w(t)&&l.isFullyInitialized()||l.isCompleteForPath(t))return ln(n,e,t,l.getNode().getChild(t),s,r,a,o);if(w(t)){let c=new A(null);return l.getNode().forEachChild(re,(d,h)=>{c=c.set(new S(d),h)}),xi(n,e,t,c,s,r,a,o)}else return e}else{let c=new A(null);return i.foreach((d,h)=>{const u=R(t,d);l.isCompleteForPath(u)&&(c=c.set(d,l.getNode().getChild(u)))}),xi(n,e,t,c,s,r,a,o)}}function Uh(n,e,t,i,s){const r=e.serverCache,o=Pr(e,r.getNode(),r.isFullyInitialized()||w(t),r.isFiltered());return Hr(n,o,t,i,Ur,s)}function Hh(n,e,t,i,s,r){let o;if(an(i,t)!=null)return e;{const a=new Si(i,e,s),l=e.eventCache.getNode();let c;if(w(t)||v(t)===".priority"){let d;if(e.serverCache.isFullyInitialized())d=on(i,$e(e));else{const h=e.serverCache.getNode();f(h instanceof g,"serverChildren would be complete if leaf node"),d=Ci(i,h)}d=d,c=n.filter.updateFullNode(l,d,r)}else{const d=v(t);let h=Ei(i,d,e.serverCache);h==null&&e.serverCache.isCompleteForChild(d)&&(h=l.getImmediateChild(d)),h!=null?c=n.filter.updateChild(l,d,h,T(t),a,r):e.eventCache.getNode().hasChild(d)?c=n.filter.updateChild(l,d,g.EMPTY_NODE,T(t),a,r):c=l,c.isEmpty()&&e.serverCache.isFullyInitialized()&&(o=on(i,$e(e)),o.isLeafNode()&&(c=n.filter.updateFullNode(c,o,r)))}return o=e.serverCache.isFullyInitialized()||an(i,E())!=null,It(e,c,o,n.filter.filtersNodes())}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class zh{constructor(e,t){this.query_=e,this.eventRegistrations_=[];const i=this.query_._queryParams,s=new hi(i.getIndex()),r=ih(i);this.processor_=Mh(r);const o=t.serverCache,a=t.eventCache,l=s.updateFullNode(g.EMPTY_NODE,o.getNode(),null),c=r.updateFullNode(g.EMPTY_NODE,a.getNode(),null),d=new be(l,o.isFullyInitialized(),s.filtersNodes()),h=new be(c,a.isFullyInitialized(),r.filtersNodes());this.viewCache_=nn(h,d),this.eventGenerator_=new ph(this.query_)}get query(){return this.query_}}function Vh(n){return n.viewCache_.serverCache.getNode()}function jh(n){return sn(n.viewCache_)}function Gh(n,e){const t=$e(n.viewCache_);return t&&(n.query._queryParams.loadsAllData()||!w(e)&&!t.getImmediateChild(v(e)).isEmpty())?t.getChild(e):null}function jr(n){return n.eventRegistrations_.length===0}function qh(n,e){n.eventRegistrations_.push(e)}function Gr(n,e,t){const i=[];if(t){f(e==null,"A cancel should cancel all event registrations.");const s=n.query._path;n.eventRegistrations_.forEach(r=>{const o=r.createCancelEvent(t,s);o&&i.push(o)})}if(e){let s=[];for(let r=0;r<n.eventRegistrations_.length;++r){const o=n.eventRegistrations_[r];if(!o.matches(e))s.push(o);else if(e.hasAnyCallback()){s=s.concat(n.eventRegistrations_.slice(r+1));break}}n.eventRegistrations_=s}else n.eventRegistrations_=[];return i}function qr(n,e,t,i){e.type===Z.MERGE&&e.source.queryId!==null&&(f($e(n.viewCache_),"We should always have a full cache before handling merges"),f(sn(n.viewCache_),"Missing event cache, even though we have a server cache"));const s=n.viewCache_,r=Fh(n.processor_,s,e,t,i);return Lh(n.processor_,r.viewCache),f(r.viewCache.serverCache.isFullyInitialized()||!s.serverCache.isFullyInitialized(),"Once a server snap is complete, it should never go back"),n.viewCache_=r.viewCache,Kr(n,r.changes,r.viewCache.eventCache.getNode(),null)}function Kh(n,e){const t=n.viewCache_.eventCache,i=[];return t.getNode().isLeafNode()||t.getNode().forEachChild(k,(r,o)=>{i.push(Ze(r,o))}),t.isFullyInitialized()&&i.push(kr(t.getNode())),Kr(n,i,t.getNode(),e)}function Kr(n,e,t,i){const s=i?[i]:n.eventRegistrations_;return _h(n.eventGenerator_,e,t,s)}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */let cn;class Qr{constructor(){this.views=new Map}}function Qh(n){f(!cn,"__referenceConstructor has already been defined"),cn=n}function Yh(){return f(cn,"Reference.ts has not been loaded"),cn}function Jh(n){return n.views.size===0}function Ti(n,e,t,i){const s=e.source.queryId;if(s!==null){const r=n.views.get(s);return f(r!=null,"SyncTree gave us an op for an invalid query."),qr(r,e,t,i)}else{let r=[];for(const o of n.views.values())r=r.concat(qr(o,e,t,i));return r}}function Yr(n,e,t,i,s){const r=e._queryIdentifier,o=n.views.get(r);if(!o){let a=on(t,s?i:null),l=!1;a?l=!0:i instanceof g?(a=Ci(t,i),l=!1):(a=g.EMPTY_NODE,l=!1);const c=nn(new be(a,l,!1),new be(i,s,!1));return new zh(e,c)}return o}function Xh(n,e,t,i,s,r){const o=Yr(n,e,i,s,r);return n.views.has(e._queryIdentifier)||n.views.set(e._queryIdentifier,o),qh(o,t),Kh(o,t)}function Zh(n,e,t,i){const s=e._queryIdentifier,r=[];let o=[];const a=Se(n);if(s==="default")for(const[l,c]of n.views.entries())o=o.concat(Gr(c,t,i)),jr(c)&&(n.views.delete(l),c.query._queryParams.loadsAllData()||r.push(c.query));else{const l=n.views.get(s);l&&(o=o.concat(Gr(l,t,i)),jr(l)&&(n.views.delete(s),l.query._queryParams.loadsAllData()||r.push(l.query)))}return a&&!Se(n)&&r.push(new(Yh())(e._repo,e._path)),{removed:r,events:o}}function Jr(n){const e=[];for(const t of n.views.values())t.query._queryParams.loadsAllData()||e.push(t);return e}function Ee(n,e){let t=null;for(const i of n.views.values())t=t||Gh(i,e);return t}function Xr(n,e){if(e._queryParams.loadsAllData())return hn(n);{const i=e._queryIdentifier;return n.views.get(i)}}function Zr(n,e){return Xr(n,e)!=null}function Se(n){return hn(n)!=null}function hn(n){for(const e of n.views.values())if(e.query._queryParams.loadsAllData())return e;return null}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */let dn;function ed(n){f(!dn,"__referenceConstructor has already been defined"),dn=n}function td(){return f(dn,"Reference.ts has not been loaded"),dn}let nd=1;class eo{constructor(e){this.listenProvider_=e,this.syncPointTree_=new A(null),this.pendingWriteTree_=Rh(),this.tagToQueryMap=new Map,this.queryToTagMap=new Map}}function ki(n,e,t,i,s){return vh(n.pendingWriteTree_,e,t,i,s),s?it(n,new Be(_i(),e,t)):[]}function id(n,e,t,i){wh(n.pendingWriteTree_,e,t,i);const s=A.fromObject(t);return it(n,new tt(_i(),e,s))}function Ie(n,e,t=!1){const i=bh(n.pendingWriteTree_,e);if(Ch(n.pendingWriteTree_,e)){let r=new A(null);return i.snap!=null?r=r.set(E(),!0):L(i.children,o=>{r=r.set(new S(o),!0)}),it(n,new tn(i.path,r,t))}else return[]}function Tt(n,e,t){return it(n,new Be(gi(),e,t))}function sd(n,e,t){const i=A.fromObject(t);return it(n,new tt(gi(),e,i))}function rd(n,e){return it(n,new Et(gi(),e))}function od(n,e,t){const i=Ni(n,t);if(i){const s=Ri(i),r=s.path,o=s.queryId,a=W(r,e),l=new Et(mi(o),a);return Pi(n,r,l)}else return[]}function un(n,e,t,i,s=!1){const r=e._path,o=n.syncPointTree_.get(r);let a=[];if(o&&(e._queryIdentifier==="default"||Zr(o,e))){const l=Zh(o,e,t,i);Jh(o)&&(n.syncPointTree_=n.syncPointTree_.remove(r));const c=l.removed;if(a=l.events,!s){const d=c.findIndex(u=>u._queryParams.loadsAllData())!==-1,h=n.syncPointTree_.findOnPath(r,(u,p)=>Se(p));if(d&&!h){const u=n.syncPointTree_.subtree(r);if(!u.isEmpty()){const p=cd(u);for(let _=0;_<p.length;++_){const C=p[_],D=C.query,fe=so(n,C);n.listenProvider_.startListening(At(D),kt(n,D),fe.hashFn,fe.onComplete)}}}!h&&c.length>0&&!i&&(d?n.listenProvider_.stopListening(At(e),null):c.forEach(u=>{const p=n.queryToTagMap.get(pn(u));n.listenProvider_.stopListening(At(u),p)}))}hd(n,c)}return a}function to(n,e,t,i){const s=Ni(n,i);if(s!=null){const r=Ri(s),o=r.path,a=r.queryId,l=W(o,e),c=new Be(mi(a),l,t);return Pi(n,o,c)}else return[]}function ad(n,e,t,i){const s=Ni(n,i);if(s){const r=Ri(s),o=r.path,a=r.queryId,l=W(o,e),c=A.fromObject(t),d=new tt(mi(a),l,c);return Pi(n,o,d)}else return[]}function Ai(n,e,t,i=!1){const s=e._path;let r=null,o=!1;n.syncPointTree_.foreachOnPath(s,(u,p)=>{const _=W(u,s);r=r||Ee(p,_),o=o||Se(p)});let a=n.syncPointTree_.get(s);a?(o=o||Se(a),r=r||Ee(a,E())):(a=new Qr,n.syncPointTree_=n.syncPointTree_.set(s,a));let l;r!=null?l=!0:(l=!1,r=g.EMPTY_NODE,n.syncPointTree_.subtree(s).foreachChild((p,_)=>{const C=Ee(_,E());C&&(r=r.updateImmediateChild(p,C))}));const c=Zr(a,e);if(!c&&!e._queryParams.loadsAllData()){const u=pn(e);f(!n.queryToTagMap.has(u),"View does not exist, but we have a tag");const p=dd();n.queryToTagMap.set(u,p),n.tagToQueryMap.set(p,u)}const d=rn(n.pendingWriteTree_,s);let h=Xh(a,e,t,d,r,l);if(!c&&!o&&!i){const u=Xr(a,e);h=h.concat(ud(n,e,u))}return h}function fn(n,e,t){const s=n.pendingWriteTree_,r=n.syncPointTree_.findOnPath(e,(o,a)=>{const l=W(o,e),c=Ee(a,l);if(c)return c});return Fr(s,e,r,t,!0)}function ld(n,e){const t=e._path;let i=null;n.syncPointTree_.foreachOnPath(t,(c,d)=>{const h=W(c,t);i=i||Ee(d,h)});let s=n.syncPointTree_.get(t);s?i=i||Ee(s,E()):(s=new Qr,n.syncPointTree_=n.syncPointTree_.set(t,s));const r=i!=null,o=r?new be(i,!0,!1):null,a=rn(n.pendingWriteTree_,e._path),l=Yr(s,e,a,r?o.getNode():g.EMPTY_NODE,r);return jh(l)}function it(n,e){return no(e,n.syncPointTree_,null,rn(n.pendingWriteTree_,E()))}function no(n,e,t,i){if(w(n.path))return io(n,e,t,i);{const s=e.get(E());t==null&&s!=null&&(t=Ee(s,E()));let r=[];const o=v(n.path),a=n.operationForChild(o),l=e.children.get(o);if(l&&a){const c=t?t.getImmediateChild(o):null,d=$r(i,o);r=r.concat(no(a,l,c,d))}return s&&(r=r.concat(Ti(s,n,i,t))),r}}function io(n,e,t,i){const s=e.get(E());t==null&&s!=null&&(t=Ee(s,E()));let r=[];return e.children.inorderTraversal((o,a)=>{const l=t?t.getImmediateChild(o):null,c=$r(i,o),d=n.operationForChild(o);d&&(r=r.concat(io(d,a,l,c)))}),s&&(r=r.concat(Ti(s,n,i,t))),r}function so(n,e){const t=e.query,i=kt(n,t);return{hashFn:()=>(Vh(e)||g.EMPTY_NODE).hash(),onComplete:s=>{if(s==="ok")return i?od(n,t._path,i):rd(n,t._path);{const r=ic(s,t);return un(n,t,null,r)}}}}function kt(n,e){const t=pn(e);return n.queryToTagMap.get(t)}function pn(n){return n._path.toString()+"$"+n._queryIdentifier}function Ni(n,e){return n.tagToQueryMap.get(e)}function Ri(n){const e=n.indexOf("$");return f(e!==-1&&e<n.length-1,"Bad queryKey."),{queryId:n.substr(e+1),path:new S(n.substr(0,e))}}function Pi(n,e,t){const i=n.syncPointTree_.get(e);f(i,"Missing sync point for query tag that we're tracking");const s=rn(n.pendingWriteTree_,e);return Ti(i,t,s,null)}function cd(n){return n.fold((e,t,i)=>{if(t&&Se(t))return[hn(t)];{let s=[];return t&&(s=Jr(t)),L(i,(r,o)=>{s=s.concat(o)}),s}})}function At(n){return n._queryParams.loadsAllData()&&!n._queryParams.isDefault()?new(td())(n._repo,n._path):n}function hd(n,e){for(let t=0;t<e.length;++t){const i=e[t];if(!i._queryParams.loadsAllData()){const s=pn(i),r=n.queryToTagMap.get(s);n.queryToTagMap.delete(s),n.tagToQueryMap.delete(r)}}}function dd(){return nd++}function ud(n,e,t){const i=e._path,s=kt(n,e),r=so(n,t),o=n.listenProvider_.startListening(At(e),s,r.hashFn,r.onComplete),a=n.syncPointTree_.subtree(i);if(s)f(!Se(a.value),"If we're adding a query, it shouldn't be shadowed");else{const l=a.fold((c,d,h)=>{if(!w(c)&&d&&Se(d))return[hn(d).query];{let u=[];return d&&(u=u.concat(Jr(d).map(p=>p.query))),L(h,(p,_)=>{u=u.concat(_)}),u}});for(let c=0;c<l.length;++c){const d=l[c];n.listenProvider_.stopListening(At(d),kt(n,d))}}return o}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Di{constructor(e){this.node_=e}getImmediateChild(e){const t=this.node_.getImmediateChild(e);return new Di(t)}node(){return this.node_}}class Oi{constructor(e,t){this.syncTree_=e,this.path_=t}getImmediateChild(e){const t=R(this.path_,e);return new Oi(this.syncTree_,t)}node(){return fn(this.syncTree_,this.path_)}}const fd=function(n){return n=n||{},n.timestamp=n.timestamp||new Date().getTime(),n},ro=function(n,e,t){if(!n||typeof n!="object")return n;if(f(".sv"in n,"Unexpected leaf node or priority contents"),typeof n[".sv"]=="string")return pd(n[".sv"],e,t);if(typeof n[".sv"]=="object")return _d(n[".sv"],e);f(!1,"Unexpected server value: "+JSON.stringify(n,null,2))},pd=function(n,e,t){switch(n){case"timestamp":return t.timestamp;default:f(!1,"Unexpected server value: "+n)}},_d=function(n,e,t){n.hasOwnProperty("increment")||f(!1,"Unexpected server value: "+JSON.stringify(n,null,2));const i=n.increment;typeof i!="number"&&f(!1,"Unexpected increment value: "+i);const s=e.node();if(f(s!==null&&typeof s<"u","Expected ChildrenNode.EMPTY_NODE for nulls"),!s.isLeafNode())return i;const o=s.getValue();return typeof o!="number"?i:o+i},oo=function(n,e,t,i){return Li(e,new Oi(t,n),i)},Mi=function(n,e,t){return Li(n,new Di(e),t)};function Li(n,e,t){const i=n.getPriority().val(),s=ro(i,e.getImmediateChild(".priority"),t);let r;if(n.isLeafNode()){const o=n,a=ro(o.getValue(),e,t);return a!==o.getValue()||s!==o.getPriority().val()?new B(a,P(s)):n}else{const o=n;return r=o,s!==o.getPriority().val()&&(r=r.updatePriority(new B(s))),o.forEachChild(k,(a,l)=>{const c=Li(l,e.getImmediateChild(a),t);c!==l&&(r=r.updateImmediateChild(a,c))}),r}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Fi{constructor(e="",t=null,i={children:{},childCount:0}){this.name=e,this.parent=t,this.node=i}}function _n(n,e){let t=e instanceof S?e:new S(e),i=n,s=v(t);for(;s!==null;){const r=Ne(i.node.children,s)||{children:{},childCount:0};i=new Fi(s,i,r),t=T(t),s=v(t)}return i}function Ue(n){return n.node.value}function Bi(n,e){n.node.value=e,$i(n)}function ao(n){return n.node.childCount>0}function gd(n){return Ue(n)===void 0&&!ao(n)}function gn(n,e){L(n.node.children,(t,i)=>{e(new Fi(t,n,i))})}function lo(n,e,t,i){t&&e(n),gn(n,s=>{lo(s,e,!0)})}function md(n,e,t){let i=n.parent;for(;i!==null;){if(e(i))return!0;i=i.parent}return!1}function Nt(n){return new S(n.parent===null?n.name:Nt(n.parent)+"/"+n.name)}function $i(n){n.parent!==null&&yd(n.parent,n.name,n)}function yd(n,e,t){const i=gd(t),s=X(n.node.children,e);i&&s?(delete n.node.children[e],n.node.childCount--,$i(n)):!i&&!s&&(n.node.children[e]=t.node,n.node.childCount++,$i(n))}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const vd=/[\[\].#$\/\u0000-\u001F\u007F]/,wd=/[\[\].#$\u0000-\u001F\u007F]/,Wi=10*1024*1024,mn=function(n){return typeof n=="string"&&n.length!==0&&!vd.test(n)},co=function(n){return typeof n=="string"&&n.length!==0&&!wd.test(n)},bd=function(n){return n&&(n=n.replace(/^\/*\.info(\/|$)/,"/")),co(n)},Rt=function(n){return n===null||typeof n=="string"||typeof n=="number"&&!Vt(n)||n&&typeof n=="object"&&X(n,".sv")},oe=function(n,e,t,i){i&&e===void 0||Pt(Re(n,"value"),e,t)},Pt=function(n,e,t){const i=t instanceof S?new Fc(t,n):t;if(e===void 0)throw new Error(n+"contains undefined "+Fe(i));if(typeof e=="function")throw new Error(n+"contains a function "+Fe(i)+" with contents = "+e.toString());if(Vt(e))throw new Error(n+"contains "+e.toString()+" "+Fe(i));if(typeof e=="string"&&e.length>Wi/3&&Ht(e)>Wi)throw new Error(n+"contains a string greater than "+Wi+" utf8 bytes "+Fe(i)+" ('"+e.substring(0,50)+"...')");if(e&&typeof e=="object"){let s=!1,r=!1;if(L(e,(o,a)=>{if(o===".value")s=!0;else if(o!==".priority"&&o!==".sv"&&(r=!0,!mn(o)))throw new Error(n+" contains an invalid key ("+o+") "+Fe(i)+`.  Keys must be non-empty strings and can't contain ".", "#", "$", "/", "[", or "]"`);Bc(i,o),Pt(n,a,i),$c(i)}),s&&r)throw new Error(n+' contains ".value" child '+Fe(i)+" in addition to actual children.")}},Cd=function(n,e){let t,i;for(t=0;t<e.length;t++){i=e[t];const r=gt(i);for(let o=0;o<r.length;o++)if(!(r[o]===".priority"&&o===r.length-1)){if(!mn(r[o]))throw new Error(n+"contains an invalid key ("+r[o]+") in path "+i.toString()+`. Keys must be non-empty strings and can't contain ".", "#", "$", "/", "[", or "]"`)}}e.sort(Lc);let s=null;for(t=0;t<e.length;t++){if(i=e[t],s!==null&&K(s,i))throw new Error(n+"contains a path "+s.toString()+" that is ancestor of another path "+i.toString());s=i}},ho=function(n,e,t,i){const s=Re(n,"values");if(!(e&&typeof e=="object")||Array.isArray(e))throw new Error(s+" must be an object containing the children to replace.");const r=[];L(e,(o,a)=>{const l=new S(o);if(Pt(s,a,R(t,l)),ni(l)===".priority"&&!Rt(a))throw new Error(s+"contains an invalid value for '"+l.toString()+"', which must be a valid Firebase priority (a string, finite number, server value, or null).");r.push(l)}),Cd(s,r)},Ui=function(n,e,t){if(Vt(e))throw new Error(Re(n,"priority")+"is "+e.toString()+", but must be a valid Firebase priority (a string, finite number, server value, or null).");if(!Rt(e))throw new Error(Re(n,"priority")+"must be a valid Firebase priority (a string, finite number, server value, or null).")},Dt=function(n,e,t,i){if(t!==void 0&&!mn(t))throw new Error(Re(n,e)+'was an invalid key = "'+t+`".  Firebase keys must be non-empty strings and can't contain ".", "#", "$", "/", "[", or "]").`)},yn=function(n,e,t,i){if(!(i&&t===void 0)&&!co(t))throw new Error(Re(n,e)+'was an invalid path = "'+t+`". Paths must be non-empty strings and can't contain ".", "#", "$", "[", or "]"`)},Ed=function(n,e,t,i){t&&(t=t.replace(/^\/*\.info(\/|$)/,"/")),yn(n,e,t,i)},te=function(n,e){if(v(e)===".info")throw new Error(n+" failed = Can't modify data under /.info/")},uo=function(n,e){const t=e.path.toString();if(typeof e.repoInfo.host!="string"||e.repoInfo.host.length===0||!mn(e.repoInfo.namespace)&&e.repoInfo.host.split(":")[0]!=="localhost"||t.length!==0&&!bd(t))throw new Error(Re(n,"url")+`must be a valid firebase URL and the path can't contain ".", "#", "$", "[", or "]".`)};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Sd{constructor(){this.eventLists_=[],this.recursionDepth_=0}}function vn(n,e){let t=null;for(let i=0;i<e.length;i++){const s=e[i],r=s.getPath();t!==null&&!ii(r,t.path)&&(n.eventLists_.push(t),t=null),t===null&&(t={events:[],path:r}),t.events.push(s)}t&&n.eventLists_.push(t)}function fo(n,e,t){vn(n,t),po(n,i=>ii(i,e))}function G(n,e,t){vn(n,t),po(n,i=>K(i,e)||K(e,i))}function po(n,e){n.recursionDepth_++;let t=!0;for(let i=0;i<n.eventLists_.length;i++){const s=n.eventLists_[i];if(s){const r=s.path;e(r)?(Id(n.eventLists_[i]),n.eventLists_[i]=null):t=!1}}t&&(n.eventLists_=[]),n.recursionDepth_--}function Id(n){for(let e=0;e<n.events.length;e++){const t=n.events[e];if(t!==null){n.events[e]=null;const i=t.getEventRunner();Me&&M("event: "+t.toString()),Qe(i)}}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const _o="repo_interrupt",xd=25;class Td{constructor(e,t,i,s){this.repoInfo_=e,this.forceRestClient_=t,this.authTokenProvider_=i,this.appCheckProvider_=s,this.dataUpdateCount=0,this.statsListener_=null,this.eventQueue_=new Sd,this.nextWriteId_=1,this.interceptServerDataCallback_=null,this.onDisconnect_=en(),this.transactionQueueTree_=new Fi,this.persistentConnection_=null,this.key=this.repoInfo_.toURLString()}toString(){return(this.repoInfo_.secure?"https://":"http://")+this.repoInfo_.host}}function kd(n,e,t){if(n.stats_=Zn(n.repoInfo_),n.forceRestClient_||ac())n.server_=new Zt(n.repoInfo_,(i,s,r,o)=>{mo(n,i,s,r,o)},n.authTokenProvider_,n.appCheckProvider_),setTimeout(()=>yo(n,!0),0);else{if(typeof t<"u"&&t!==null){if(typeof t!="object")throw new Error("Only objects are supported for option databaseAuthVariableOverride");try{O(t)}catch(i){throw new Error("Invalid authOverride provided: "+i)}}n.persistentConnection_=new Q(n.repoInfo_,e,(i,s,r,o)=>{mo(n,i,s,r,o)},i=>{yo(n,i)},i=>{Ad(n,i)},n.authTokenProvider_,n.appCheckProvider_,t),n.server_=n.persistentConnection_}n.authTokenProvider_.addTokenChangeListener(i=>{n.server_.refreshAuthToken(i)}),n.appCheckProvider_.addTokenChangeListener(i=>{n.server_.refreshAppCheckToken(i.token)}),n.statsReporter_=uc(n.repoInfo_,()=>new fh(n.stats_,n.server_)),n.infoData_=new lh,n.infoSyncTree_=new eo({startListening:(i,s,r,o)=>{let a=[];const l=n.infoData_.getNode(i._path);return l.isEmpty()||(a=Tt(n.infoSyncTree_,i._path,l),setTimeout(()=>{o("ok")},0)),a},stopListening:()=>{}}),Hi(n,"connected",!1),n.serverSyncTree_=new eo({startListening:(i,s,r,o)=>(n.server_.listen(i,r,s,(a,l)=>{const c=o(a,l);G(n.eventQueue_,i._path,c)}),[]),stopListening:(i,s)=>{n.server_.unlisten(i,s)}})}function go(n){const t=n.infoData_.getNode(new S(".info/serverTimeOffset")).val()||0;return new Date().getTime()+t}function Ot(n){return fd({timestamp:go(n)})}function mo(n,e,t,i,s){n.dataUpdateCount++;const r=new S(e);t=n.interceptServerDataCallback_?n.interceptServerDataCallback_(e,t):t;let o=[];if(s)if(i){const l=Wt(t,c=>P(c));o=ad(n.serverSyncTree_,r,l,s)}else{const l=P(t);o=to(n.serverSyncTree_,r,l,s)}else if(i){const l=Wt(t,c=>P(c));o=sd(n.serverSyncTree_,r,l)}else{const l=P(t);o=Tt(n.serverSyncTree_,r,l)}let a=r;o.length>0&&(a=rt(n,r)),G(n.eventQueue_,a,o)}function yo(n,e){Hi(n,"connected",e),e===!1&&Pd(n)}function Ad(n,e){L(e,(t,i)=>{Hi(n,t,i)})}function Hi(n,e,t){const i=new S("/.info/"+e),s=P(t);n.infoData_.updateSnapshot(i,s);const r=Tt(n.infoSyncTree_,i,s);G(n.eventQueue_,i,r)}function wn(n){return n.nextWriteId_++}function Nd(n,e,t){const i=ld(n.serverSyncTree_,e);return i!=null?Promise.resolve(i):n.server_.get(e).then(s=>{const r=P(s).withIndex(e._queryParams.getIndex());Ai(n.serverSyncTree_,e,t,!0);let o;if(e._queryParams.loadsAllData())o=Tt(n.serverSyncTree_,e._path,r);else{const a=kt(n.serverSyncTree_,e);o=to(n.serverSyncTree_,e._path,r,a)}return G(n.eventQueue_,e._path,o),un(n.serverSyncTree_,e,t,null,!0),r},s=>(st(n,"get for query "+O(e)+" failed: "+s),Promise.reject(new Error(s))))}function zi(n,e,t,i,s){st(n,"set",{path:e.toString(),value:t,priority:i});const r=Ot(n),o=P(t,i),a=fn(n.serverSyncTree_,e),l=Mi(o,a,r),c=wn(n),d=ki(n.serverSyncTree_,e,l,c,!0);vn(n.eventQueue_,d),n.server_.put(e.toString(),o.val(!0),(u,p)=>{const _=u==="ok";_||$("set at "+e+" failed: "+u);const C=Ie(n.serverSyncTree_,c,!_);G(n.eventQueue_,e,C),xe(n,s,u,p)});const h=Gi(n,e);rt(n,h),G(n.eventQueue_,h,[])}function Rd(n,e,t,i){st(n,"update",{path:e.toString(),value:t});let s=!0;const r=Ot(n),o={};if(L(t,(a,l)=>{s=!1,o[a]=oo(R(e,a),P(l),n.serverSyncTree_,r)}),s)M("update() called with empty data.  Don't do anything."),xe(n,i,"ok",void 0);else{const a=wn(n),l=id(n.serverSyncTree_,e,o,a);vn(n.eventQueue_,l),n.server_.merge(e.toString(),t,(c,d)=>{const h=c==="ok";h||$("update at "+e+" failed: "+c);const u=Ie(n.serverSyncTree_,a,!h),p=u.length>0?rt(n,e):e;G(n.eventQueue_,p,u),xe(n,i,c,d)}),L(t,c=>{const d=Gi(n,R(e,c));rt(n,d)}),G(n.eventQueue_,e,[])}}function Pd(n){st(n,"onDisconnectEvents");const e=Ot(n),t=en();pi(n.onDisconnect_,E(),(s,r)=>{const o=oo(s,r,n.serverSyncTree_,e);et(t,s,o)});let i=[];pi(t,E(),(s,r)=>{i=i.concat(Tt(n.serverSyncTree_,s,r));const o=Gi(n,s);rt(n,o)}),n.onDisconnect_=en(),G(n.eventQueue_,E(),i)}function Dd(n,e,t){n.server_.onDisconnectCancel(e.toString(),(i,s)=>{i==="ok"&&fi(n.onDisconnect_,e),xe(n,t,i,s)})}function vo(n,e,t,i){const s=P(t);n.server_.onDisconnectPut(e.toString(),s.val(!0),(r,o)=>{r==="ok"&&et(n.onDisconnect_,e,s),xe(n,i,r,o)})}function Od(n,e,t,i,s){const r=P(t,i);n.server_.onDisconnectPut(e.toString(),r.val(!0),(o,a)=>{o==="ok"&&et(n.onDisconnect_,e,r),xe(n,s,o,a)})}function Md(n,e,t,i){if(Nn(t)){M("onDisconnect().update() called with empty data.  Don't do anything."),xe(n,i,"ok",void 0);return}n.server_.onDisconnectMerge(e.toString(),t,(s,r)=>{s==="ok"&&L(t,(o,a)=>{const l=P(a);et(n.onDisconnect_,R(e,o),l)}),xe(n,i,s,r)})}function Ld(n,e,t){let i;v(e._path)===".info"?i=Ai(n.infoSyncTree_,e,t):i=Ai(n.serverSyncTree_,e,t),fo(n.eventQueue_,e._path,i)}function Vi(n,e,t){let i;v(e._path)===".info"?i=un(n.infoSyncTree_,e,t):i=un(n.serverSyncTree_,e,t),fo(n.eventQueue_,e._path,i)}function wo(n){n.persistentConnection_&&n.persistentConnection_.interrupt(_o)}function Fd(n){n.persistentConnection_&&n.persistentConnection_.resume(_o)}function st(n,...e){let t="";n.persistentConnection_&&(t=n.persistentConnection_.id+":"),M(t,...e)}function xe(n,e,t,i){e&&Qe(()=>{if(t==="ok")e(null);else{const s=(t||"error").toUpperCase();let r=s;i&&(r+=": "+i);const o=new Error(r);o.code=s,e(o)}})}function Bd(n,e,t,i,s,r){st(n,"transaction on "+e);const o={path:e,update:t,onComplete:i,status:null,order:Bs(),applyLocally:r,retryCount:0,unwatcher:s,abortReason:null,currentWriteId:null,currentInputSnapshot:null,currentOutputSnapshotRaw:null,currentOutputSnapshotResolved:null},a=ji(n,e,void 0);o.currentInputSnapshot=a;const l=o.update(a.val());if(l===void 0)o.unwatcher(),o.currentOutputSnapshotRaw=null,o.currentOutputSnapshotResolved=null,o.onComplete&&o.onComplete(null,!1,o.currentInputSnapshot);else{Pt("transaction failed: Data returned ",l,o.path),o.status=0;const c=_n(n.transactionQueueTree_,e),d=Ue(c)||[];d.push(o),Bi(c,d);let h;typeof l=="object"&&l!==null&&X(l,".priority")?(h=Ne(l,".priority"),f(Rt(h),"Invalid priority returned by transaction. Priority must be a valid string, finite number, server value, or null.")):h=(fn(n.serverSyncTree_,e)||g.EMPTY_NODE).getPriority().val();const u=Ot(n),p=P(l,h),_=Mi(p,a,u);o.currentOutputSnapshotRaw=p,o.currentOutputSnapshotResolved=_,o.currentWriteId=wn(n);const C=ki(n.serverSyncTree_,e,_,o.currentWriteId,o.applyLocally);G(n.eventQueue_,e,C),bn(n,n.transactionQueueTree_)}}function ji(n,e,t){return fn(n.serverSyncTree_,e,t)||g.EMPTY_NODE}function bn(n,e=n.transactionQueueTree_){if(e||Cn(n,e),Ue(e)){const t=Co(n,e);f(t.length>0,"Sending zero length transaction queue"),t.every(s=>s.status===0)&&$d(n,Nt(e),t)}else ao(e)&&gn(e,t=>{bn(n,t)})}function $d(n,e,t){const i=t.map(c=>c.currentWriteId),s=ji(n,e,i);let r=s;const o=s.hash();for(let c=0;c<t.length;c++){const d=t[c];f(d.status===0,"tryToSendTransactionQueue_: items in queue should all be run."),d.status=1,d.retryCount++;const h=W(e,d.path);r=r.updateChild(h,d.currentOutputSnapshotRaw)}const a=r.val(!0),l=e;n.server_.put(l.toString(),a,c=>{st(n,"transaction put response",{path:l.toString(),status:c});let d=[];if(c==="ok"){const h=[];for(let u=0;u<t.length;u++)t[u].status=2,d=d.concat(Ie(n.serverSyncTree_,t[u].currentWriteId)),t[u].onComplete&&h.push(()=>t[u].onComplete(null,!0,t[u].currentOutputSnapshotResolved)),t[u].unwatcher();Cn(n,_n(n.transactionQueueTree_,e)),bn(n,n.transactionQueueTree_),G(n.eventQueue_,e,d);for(let u=0;u<h.length;u++)Qe(h[u])}else{if(c==="datastale")for(let h=0;h<t.length;h++)t[h].status===3?t[h].status=4:t[h].status=0;else{$("transaction at "+l.toString()+" failed: "+c);for(let h=0;h<t.length;h++)t[h].status=4,t[h].abortReason=c}rt(n,e)}},o)}function rt(n,e){const t=bo(n,e),i=Nt(t),s=Co(n,t);return Wd(n,s,i),i}function Wd(n,e,t){if(e.length===0)return;const i=[];let s=[];const o=e.filter(a=>a.status===0).map(a=>a.currentWriteId);for(let a=0;a<e.length;a++){const l=e[a],c=W(t,l.path);let d=!1,h;if(f(c!==null,"rerunTransactionsUnderNode_: relativePath should not be null."),l.status===4)d=!0,h=l.abortReason,s=s.concat(Ie(n.serverSyncTree_,l.currentWriteId,!0));else if(l.status===0)if(l.retryCount>=xd)d=!0,h="maxretry",s=s.concat(Ie(n.serverSyncTree_,l.currentWriteId,!0));else{const u=ji(n,l.path,o);l.currentInputSnapshot=u;const p=e[a].update(u.val());if(p!==void 0){Pt("transaction failed: Data returned ",p,l.path);let _=P(p);typeof p=="object"&&p!=null&&X(p,".priority")||(_=_.updatePriority(u.getPriority()));const D=l.currentWriteId,fe=Ot(n),ze=Mi(_,u,fe);l.currentOutputSnapshotRaw=_,l.currentOutputSnapshotResolved=ze,l.currentWriteId=wn(n),o.splice(o.indexOf(D),1),s=s.concat(ki(n.serverSyncTree_,l.path,ze,l.currentWriteId,l.applyLocally)),s=s.concat(Ie(n.serverSyncTree_,D,!0))}else d=!0,h="nodata",s=s.concat(Ie(n.serverSyncTree_,l.currentWriteId,!0))}G(n.eventQueue_,t,s),s=[],d&&(e[a].status=2,(function(u){setTimeout(u,Math.floor(0))})(e[a].unwatcher),e[a].onComplete&&(h==="nodata"?i.push(()=>e[a].onComplete(null,!1,e[a].currentInputSnapshot)):i.push(()=>e[a].onComplete(new Error(h),!1,null))))}Cn(n,n.transactionQueueTree_);for(let a=0;a<i.length;a++)Qe(i[a]);bn(n,n.transactionQueueTree_)}function bo(n,e){let t,i=n.transactionQueueTree_;for(t=v(e);t!==null&&Ue(i)===void 0;)i=_n(i,t),e=T(e),t=v(e);return i}function Co(n,e){const t=[];return Eo(n,e,t),t.sort((i,s)=>i.order-s.order),t}function Eo(n,e,t){const i=Ue(e);if(i)for(let s=0;s<i.length;s++)t.push(i[s]);gn(e,s=>{Eo(n,s,t)})}function Cn(n,e){const t=Ue(e);if(t){let i=0;for(let s=0;s<t.length;s++)t[s].status!==2&&(t[i]=t[s],i++);t.length=i,Bi(e,t.length>0?t:void 0)}gn(e,i=>{Cn(n,i)})}function Gi(n,e){const t=Nt(bo(n,e)),i=_n(n.transactionQueueTree_,e);return md(i,s=>{qi(n,s)}),qi(n,i),lo(i,s=>{qi(n,s)}),t}function qi(n,e){const t=Ue(e);if(t){const i=[];let s=[],r=-1;for(let o=0;o<t.length;o++)t[o].status===3||(t[o].status===1?(f(r===o-1,"All SENT items should be at beginning of queue."),r=o,t[o].status=3,t[o].abortReason="set"):(f(t[o].status===0,"Unexpected transaction status in abort"),t[o].unwatcher(),s=s.concat(Ie(n.serverSyncTree_,t[o].currentWriteId,!0)),t[o].onComplete&&i.push(t[o].onComplete.bind(null,new Error("set"),!1,null))));r===-1?Bi(e,void 0):t.length=r+1,G(n.eventQueue_,Nt(e),s);for(let o=0;o<i.length;o++)Qe(i[o])}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Ud(n){let e="";const t=n.split("/");for(let i=0;i<t.length;i++)if(t[i].length>0){let s=t[i];try{s=decodeURIComponent(s.replace(/\+/g," "))}catch{}e+="/"+s}return e}function Hd(n){const e={};n.charAt(0)==="?"&&(n=n.substring(1));for(const t of n.split("&")){if(t.length===0)continue;const i=t.split("=");i.length===2?e[decodeURIComponent(i[0])]=decodeURIComponent(i[1]):$(`Invalid query segment '${t}' in query '${n}'`)}return e}const Ki=function(n,e){const t=zd(n),i=t.namespace;t.domain==="firebase.com"&&se(t.host+" is no longer supported. Please use <YOUR FIREBASE>.firebaseio.com instead"),(!i||i==="undefined")&&t.domain!=="localhost"&&se("Cannot parse Firebase url. Please use https://<YOUR FIREBASE>.firebaseio.com"),t.secure||Xl();const s=t.scheme==="ws"||t.scheme==="wss";return{repoInfo:new er(t.host,t.secure,i,s,e,"",i!==t.subdomain),path:new S(t.pathString)}},zd=function(n){let e="",t="",i="",s="",r="",o=!0,a="https",l=443;if(typeof n=="string"){let c=n.indexOf("//");c>=0&&(a=n.substring(0,c-1),n=n.substring(c+2));let d=n.indexOf("/");d===-1&&(d=n.length);let h=n.indexOf("?");h===-1&&(h=n.length),e=n.substring(0,Math.min(d,h)),d<h&&(s=Ud(n.substring(d,h)));const u=Hd(n.substring(Math.min(n.length,h)));c=e.indexOf(":"),c>=0?(o=a==="https"||a==="wss",l=parseInt(e.substring(c+1),10)):c=e.length;const p=e.slice(0,c);if(p.toLowerCase()==="localhost")t="localhost";else if(p.split(".").length<=2)t=p;else{const _=e.indexOf(".");i=e.substring(0,_).toLowerCase(),t=e.substring(_+1),r=i}"ns"in u&&(r=u.ns)}return{host:e,port:l,domain:t,subdomain:i,secure:o,scheme:a,pathString:s,namespace:r}};/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const So="-0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz",Vd=(function(){let n=0;const e=[];return function(t){const i=t===n;n=t;let s;const r=new Array(8);for(s=7;s>=0;s--)r[s]=So.charAt(t%64),t=Math.floor(t/64);f(t===0,"Cannot push at time == 0");let o=r.join("");if(i){for(s=11;s>=0&&e[s]===63;s--)e[s]=0;e[s]++}else for(s=0;s<12;s++)e[s]=Math.floor(Math.random()*64);for(s=0;s<12;s++)o+=So.charAt(e[s]);return f(o.length===20,"nextPushId: Length should be 20."),o}})();/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Io{constructor(e,t,i,s){this.eventType=e,this.eventRegistration=t,this.snapshot=i,this.prevName=s}getPath(){const e=this.snapshot.ref;return this.eventType==="value"?e._path:e.parent._path}getEventType(){return this.eventType}getEventRunner(){return this.eventRegistration.getEventRunner(this)}toString(){return this.getPath().toString()+":"+this.eventType+":"+O(this.snapshot.exportVal())}}class xo{constructor(e,t,i){this.eventRegistration=e,this.error=t,this.path=i}getPath(){return this.path}getEventType(){return"cancel"}getEventRunner(){return this.eventRegistration.getEventRunner(this)}toString(){return this.path.toString()+":cancel"}}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Qi{constructor(e,t){this.snapshotCallback=e,this.cancelCallback=t}onValue(e,t){this.snapshotCallback.call(null,e,t)}onCancel(e){return f(this.hasCancelCallback,"Raising a cancel event on a listener with no cancel callback"),this.cancelCallback.call(null,e)}get hasCancelCallback(){return!!this.cancelCallback}matches(e){return this.snapshotCallback===e.snapshotCallback||this.snapshotCallback.userCallback!==void 0&&this.snapshotCallback.userCallback===e.snapshotCallback.userCallback&&this.snapshotCallback.context===e.snapshotCallback.context}}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class To{constructor(e,t){this._repo=e,this._path=t}cancel(){const e=new j;return Dd(this._repo,this._path,e.wrapCallback(()=>{})),e.promise}remove(){te("OnDisconnect.remove",this._path);const e=new j;return vo(this._repo,this._path,null,e.wrapCallback(()=>{})),e.promise}set(e){te("OnDisconnect.set",this._path),oe("OnDisconnect.set",e,this._path,!1);const t=new j;return vo(this._repo,this._path,e,t.wrapCallback(()=>{})),t.promise}setWithPriority(e,t){te("OnDisconnect.setWithPriority",this._path),oe("OnDisconnect.setWithPriority",e,this._path,!1),Ui("OnDisconnect.setWithPriority",t);const i=new j;return Od(this._repo,this._path,e,t,i.wrapCallback(()=>{})),i.promise}update(e){te("OnDisconnect.update",this._path),ho("OnDisconnect.update",e,this._path);const t=new j;return Md(this._repo,this._path,e,t.wrapCallback(()=>{})),t.promise}}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class V{constructor(e,t,i,s){this._repo=e,this._path=t,this._queryParams=i,this._orderByCalled=s}get key(){return w(this._path)?null:ni(this._path)}get ref(){return new Y(this._repo,this._path)}get _queryIdentifier(){const e=Nr(this._queryParams),t=Kn(e);return t==="{}"?"default":t}get _queryObject(){return Nr(this._queryParams)}isEqual(e){if(e=U(e),!(e instanceof V))return!1;const t=this._repo===e._repo,i=ii(this._path,e._path),s=this._queryIdentifier===e._queryIdentifier;return t&&i&&s}toJSON(){return this.toString()}toString(){return this._repo.toString()+Mc(this._path)}}function En(n,e){if(n._orderByCalled===!0)throw new Error(e+": You can't combine multiple orderBy calls.")}function Te(n){let e=null,t=null;if(n.hasStart()&&(e=n.getIndexStartValue()),n.hasEnd()&&(t=n.getIndexEndValue()),n.getIndex()===re){const i="Query: When ordering by key, you may only pass one argument to startAt(), endAt(), or equalTo().",s="Query: When ordering by key, the argument passed to startAt(), startAfter(), endAt(), endBefore(), or equalTo() must be a string.";if(n.hasStart()){if(n.getIndexStartName()!==ye)throw new Error(i);if(typeof e!="string")throw new Error(s)}if(n.hasEnd()){if(n.getIndexEndName()!==de)throw new Error(i);if(typeof t!="string")throw new Error(s)}}else if(n.getIndex()===k){if(e!=null&&!Rt(e)||t!=null&&!Rt(t))throw new Error("Query: When ordering by priority, the first argument passed to startAt(), startAfter() endAt(), endBefore(), or equalTo() must be a valid priority value (null, a number, or a string).")}else if(f(n.getIndex()instanceof li||n.getIndex()===ci,"unknown index type."),e!=null&&typeof e=="object"||t!=null&&typeof t=="object")throw new Error("Query: First argument passed to startAt(), startAfter(), endAt(), endBefore(), or equalTo() cannot be an object.")}function Sn(n){if(n.hasStart()&&n.hasEnd()&&n.hasLimit()&&!n.hasAnchoredLimit())throw new Error("Query: Can't combine startAt(), startAfter(), endAt(), endBefore(), and limit(). Use limitToFirst() or limitToLast() instead.")}class Y extends V{constructor(e,t){super(e,t,new Jt,!1)}get parent(){const e=yr(this._path);return e===null?null:new Y(this._repo,e)}get root(){let e=this;for(;e.parent!==null;)e=e.parent;return e}}class ke{constructor(e,t,i){this._node=e,this.ref=t,this._index=i}get priority(){return this._node.getPriority().val()}get key(){return this.ref.key}get size(){return this._node.numChildren()}child(e){const t=new S(e),i=He(this.ref,e);return new ke(this._node.getChild(t),i,k)}exists(){return!this._node.isEmpty()}exportVal(){return this._node.val(!0)}forEach(e){return this._node.isLeafNode()?!1:!!this._node.forEachChild(this._index,(i,s)=>e(new ke(s,He(this.ref,i),k)))}hasChild(e){const t=new S(e);return!this._node.getChild(t).isEmpty()}hasChildren(){return this._node.isLeafNode()?!1:!this._node.isEmpty()}toJSON(){return this.exportVal()}val(){return this._node.val()}}function ko(n,e){return n=U(n),n._checkNotDeleted("ref"),e!==void 0?He(n._root,e):n._root}function jd(n,e){n=U(n),n._checkNotDeleted("refFromURL");const t=Ki(e,n._repo.repoInfo_.nodeAdmin);uo("refFromURL",t);const i=t.repoInfo;return!n._repo.repoInfo_.isCustomHost()&&i.host!==n._repo.repoInfo_.host&&se("refFromURL: Host name does not match the current database: (found "+i.host+" but expected "+n._repo.repoInfo_.host+")"),ko(n,t.path.toString())}function He(n,e){return n=U(n),v(n._path)===null?Ed("child","path",e,!1):yn("child","path",e,!1),new Y(n._repo,R(n._path,e))}function Gd(n){return n=U(n),new To(n._repo,n._path)}function qd(n,e){n=U(n),te("push",n._path),oe("push",e,n._path,!0);const t=go(n._repo),i=Vd(t),s=He(n,i),r=He(n,i);let o;return e!=null?o=Yi(r,e).then(()=>r):o=Promise.resolve(r),s.then=o.then.bind(o),s.catch=o.then.bind(o,void 0),s}function Kd(n){return te("remove",n._path),Yi(n,null)}function Yi(n,e){n=U(n),te("set",n._path),oe("set",e,n._path,!1);const t=new j;return zi(n._repo,n._path,e,null,t.wrapCallback(()=>{})),t.promise}function Qd(n,e){n=U(n),te("setPriority",n._path),Ui("setPriority",e);const t=new j;return zi(n._repo,R(n._path,".priority"),e,null,t.wrapCallback(()=>{})),t.promise}function Yd(n,e,t){if(te("setWithPriority",n._path),oe("setWithPriority",e,n._path,!1),Ui("setWithPriority",t),n.key===".length"||n.key===".keys")throw"setWithPriority failed: "+n.key+" is a read-only object.";const i=new j;return zi(n._repo,n._path,e,t,i.wrapCallback(()=>{})),i.promise}function Jd(n,e){ho("update",e,n._path);const t=new j;return Rd(n._repo,n._path,e,t.wrapCallback(()=>{})),t.promise}function Xd(n){n=U(n);const e=new Qi(()=>{}),t=new Mt(e);return Nd(n._repo,n,t).then(i=>new ke(i,new Y(n._repo,n._path),n._queryParams.getIndex()))}class Mt{constructor(e){this.callbackContext=e}respondsTo(e){return e==="value"}createEvent(e,t){const i=t._queryParams.getIndex();return new Io("value",this,new ke(e.snapshotNode,new Y(t._repo,t._path),i))}getEventRunner(e){return e.getEventType()==="cancel"?()=>this.callbackContext.onCancel(e.error):()=>this.callbackContext.onValue(e.snapshot,null)}createCancelEvent(e,t){return this.callbackContext.hasCancelCallback?new xo(this,e,t):null}matches(e){return e instanceof Mt?!e.callbackContext||!this.callbackContext?!0:e.callbackContext.matches(this.callbackContext):!1}hasAnyCallback(){return this.callbackContext!==null}}class In{constructor(e,t){this.eventType=e,this.callbackContext=t}respondsTo(e){let t=e==="children_added"?"child_added":e;return t=t==="children_removed"?"child_removed":t,this.eventType===t}createCancelEvent(e,t){return this.callbackContext.hasCancelCallback?new xo(this,e,t):null}createEvent(e,t){f(e.childName!=null,"Child events should have a childName.");const i=He(new Y(t._repo,t._path),e.childName),s=t._queryParams.getIndex();return new Io(e.type,this,new ke(e.snapshotNode,i,s),e.prevName)}getEventRunner(e){return e.getEventType()==="cancel"?()=>this.callbackContext.onCancel(e.error):()=>this.callbackContext.onValue(e.snapshot,e.prevName)}matches(e){return e instanceof In?this.eventType===e.eventType&&(!this.callbackContext||!e.callbackContext||this.callbackContext.matches(e.callbackContext)):!1}hasAnyCallback(){return!!this.callbackContext}}function Lt(n,e,t,i,s){let r;if(typeof i=="object"&&(r=void 0,s=i),typeof i=="function"&&(r=i),s&&s.onlyOnce){const l=t,c=(d,h)=>{Vi(n._repo,n,a),l(d,h)};c.userCallback=t.userCallback,c.context=t.context,t=c}const o=new Qi(t,r||void 0),a=e==="value"?new Mt(o):new In(e,o);return Ld(n._repo,n,a),()=>Vi(n._repo,n,a)}function Ao(n,e,t,i){return Lt(n,"value",e,t,i)}function Zd(n,e,t,i){return Lt(n,"child_added",e,t,i)}function eu(n,e,t,i){return Lt(n,"child_changed",e,t,i)}function tu(n,e,t,i){return Lt(n,"child_moved",e,t,i)}function nu(n,e,t,i){return Lt(n,"child_removed",e,t,i)}function iu(n,e,t){let i=null;const s=t?new Qi(t):null;e==="value"?i=new Mt(s):e&&(i=new In(e,s)),Vi(n._repo,n,i)}class J{}class No extends J{constructor(e,t){super(),this._value=e,this._key=t,this.type="endAt"}_apply(e){oe("endAt",this._value,e._path,!0);const t=ui(e._queryParams,this._value,this._key);if(Sn(t),Te(t),e._queryParams.hasEnd())throw new Error("endAt: Starting point was already set (by another call to endAt, endBefore or equalTo).");return new V(e._repo,e._path,t,e._orderByCalled)}}function su(n,e){return Dt("endAt","key",e),new No(n,e)}class ru extends J{constructor(e,t){super(),this._value=e,this._key=t,this.type="endBefore"}_apply(e){oe("endBefore",this._value,e._path,!1);const t=ah(e._queryParams,this._value,this._key);if(Sn(t),Te(t),e._queryParams.hasEnd())throw new Error("endBefore: Starting point was already set (by another call to endAt, endBefore or equalTo).");return new V(e._repo,e._path,t,e._orderByCalled)}}function ou(n,e){return Dt("endBefore","key",e),new ru(n,e)}class Ro extends J{constructor(e,t){super(),this._value=e,this._key=t,this.type="startAt"}_apply(e){oe("startAt",this._value,e._path,!0);const t=di(e._queryParams,this._value,this._key);if(Sn(t),Te(t),e._queryParams.hasStart())throw new Error("startAt: Starting point was already set (by another call to startAt, startBefore or equalTo).");return new V(e._repo,e._path,t,e._orderByCalled)}}function au(n=null,e){return Dt("startAt","key",e),new Ro(n,e)}class lu extends J{constructor(e,t){super(),this._value=e,this._key=t,this.type="startAfter"}_apply(e){oe("startAfter",this._value,e._path,!1);const t=oh(e._queryParams,this._value,this._key);if(Sn(t),Te(t),e._queryParams.hasStart())throw new Error("startAfter: Starting point was already set (by another call to startAt, startAfter, or equalTo).");return new V(e._repo,e._path,t,e._orderByCalled)}}function cu(n,e){return Dt("startAfter","key",e),new lu(n,e)}class hu extends J{constructor(e){super(),this._limit=e,this.type="limitToFirst"}_apply(e){if(e._queryParams.hasLimit())throw new Error("limitToFirst: Limit was already set (by another call to limitToFirst or limitToLast).");return new V(e._repo,e._path,sh(e._queryParams,this._limit),e._orderByCalled)}}function du(n){if(typeof n!="number"||Math.floor(n)!==n||n<=0)throw new Error("limitToFirst: First argument must be a positive integer.");return new hu(n)}class uu extends J{constructor(e){super(),this._limit=e,this.type="limitToLast"}_apply(e){if(e._queryParams.hasLimit())throw new Error("limitToLast: Limit was already set (by another call to limitToFirst or limitToLast).");return new V(e._repo,e._path,rh(e._queryParams,this._limit),e._orderByCalled)}}function fu(n){if(typeof n!="number"||Math.floor(n)!==n||n<=0)throw new Error("limitToLast: First argument must be a positive integer.");return new uu(n)}class pu extends J{constructor(e){super(),this._path=e,this.type="orderByChild"}_apply(e){En(e,"orderByChild");const t=new S(this._path);if(w(t))throw new Error("orderByChild: cannot pass in empty path. Use orderByValue() instead.");const i=new li(t),s=Xt(e._queryParams,i);return Te(s),new V(e._repo,e._path,s,!0)}}function _u(n){if(n==="$key")throw new Error('orderByChild: "$key" is invalid.  Use orderByKey() instead.');if(n==="$priority")throw new Error('orderByChild: "$priority" is invalid.  Use orderByPriority() instead.');if(n==="$value")throw new Error('orderByChild: "$value" is invalid.  Use orderByValue() instead.');return yn("orderByChild","path",n,!1),new pu(n)}class gu extends J{constructor(){super(...arguments),this.type="orderByKey"}_apply(e){En(e,"orderByKey");const t=Xt(e._queryParams,re);return Te(t),new V(e._repo,e._path,t,!0)}}function mu(){return new gu}class yu extends J{constructor(){super(...arguments),this.type="orderByPriority"}_apply(e){En(e,"orderByPriority");const t=Xt(e._queryParams,k);return Te(t),new V(e._repo,e._path,t,!0)}}function vu(){return new yu}class wu extends J{constructor(){super(...arguments),this.type="orderByValue"}_apply(e){En(e,"orderByValue");const t=Xt(e._queryParams,ci);return Te(t),new V(e._repo,e._path,t,!0)}}function bu(){return new wu}class Cu extends J{constructor(e,t){super(),this._value=e,this._key=t,this.type="equalTo"}_apply(e){if(oe("equalTo",this._value,e._path,!1),e._queryParams.hasStart())throw new Error("equalTo: Starting point was already set (by another call to startAt/startAfter or equalTo).");if(e._queryParams.hasEnd())throw new Error("equalTo: Ending point was already set (by another call to endAt/endBefore or equalTo).");return new No(this._value,this._key)._apply(new Ro(this._value,this._key)._apply(e))}}function Eu(n,e){return Dt("equalTo","key",e),new Cu(n,e)}function Su(n,...e){let t=U(n);for(const i of e)t=i._apply(t);return t}Qh(Y),ed(Y);/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Iu="FIREBASE_DATABASE_EMULATOR_HOST",Ji={};let Po=!1;function xu(n,e,t,i){const s=e.lastIndexOf(":"),r=e.substring(0,s),o=fs(r);n.repoInfo_=new er(e,o,n.repoInfo_.namespace,n.repoInfo_.webSocketOnly,n.repoInfo_.nodeAdmin,n.repoInfo_.persistenceKey,n.repoInfo_.includeNamespaceInQueryParams,!0,t),i&&(n.authTokenProvider_=i)}function Xi(n,e,t,i,s){let r=i||n.options.databaseURL;r===void 0&&(n.options.projectId||se("Can't determine Firebase Database URL. Be sure to include  a Project ID when calling firebase.initializeApp()."),M("Using default host for project ",n.options.projectId),r=`${n.options.projectId}-default-rtdb.firebaseio.com`);let o=Ki(r,s),a=o.repoInfo,l,c;typeof process<"u"&&process.env&&(c=process.env[Iu]),c?(l=!0,r=`http://${c}?ns=${a.namespace}`,o=Ki(r,s),a=o.repoInfo):l=!o.repoInfo.secure;const d=s&&l?new Ye(Ye.OWNER):new cc(n.name,n.options,e);uo("Invalid Firebase Database URL",o),w(o.path)||se("Database URL must point to the root of a Firebase Database (not including a child path).");const h=ku(a,n,d,new lc(n,t));return new Do(h,n)}function Tu(n,e){const t=Ji[e];(!t||t[n.key]!==n)&&se(`Database ${e}(${n.repoInfo_}) has already been deleted.`),wo(n),delete t[n.key]}function ku(n,e,t,i){let s=Ji[e.name];s||(s={},Ji[e.name]=s);let r=s[n.toURLString()];return r&&se("Database initialized multiple times. Please make sure the format of the database URL matches with each database() call."),r=new Td(n,Po,t,i),s[n.toURLString()]=r,r}function Au(n){Po=n}class Do{constructor(e,t){this._repoInternal=e,this.app=t,this.type="database",this._instanceStarted=!1}get _repo(){return this._instanceStarted||(kd(this._repoInternal,this.app.options.appId,this.app.options.databaseAuthVariableOverride),this._instanceStarted=!0),this._repoInternal}get _root(){return this._rootInternal||(this._rootInternal=new Y(this._repo,E())),this._rootInternal}_delete(){return this._rootInternal!==null&&(Tu(this._repo,this.app.name),this._repoInternal=null,this._rootInternal=null),Promise.resolve()}_checkNotDeleted(e){this._rootInternal===null&&se("Cannot call "+e+" on a deleted database.")}}function Oo(){Je.IS_TRANSPORT_INITIALIZED&&$("Transport has already been initialized. Please call this function before calling ref or setting up a listener")}function Nu(){Oo(),ve.forceDisallow()}function Ru(){Oo(),q.forceDisallow(),ve.forceAllow()}function Pu(n=Ts(),e){const t=Hn(n,"database").getImmediate({identifier:e});if(!t._instanceStarted){const i=ya("database");i&&Mo(t,...i)}return t}function Mo(n,e,t,i={}){n=U(n),n._checkNotDeleted("useEmulator");const s=`${e}:${t}`,r=n._repoInternal;if(n._instanceStarted){if(s===n._repoInternal.repoInfo_.host&&Ut(i,r.repoInfo_.emulatorOptions))return;se("connectDatabaseEmulator() cannot initialize or alter the emulator configuration after the database instance has started.")}let o;if(r.repoInfo_.nodeAdmin)i.mockUserToken&&se('mockUserToken is not supported by the Admin SDK. For client access with mock users, please use the "firebase" package instead of "firebase-admin".'),o=new Ye(Ye.OWNER);else if(i.mockUserToken){const a=typeof i.mockUserToken=="string"?i.mockUserToken:va(i.mockUserToken,n.app.options.projectId);o=new Ye(a)}fs(e)&&Da(e),xu(r,s,i,o)}function Du(n){n=U(n),n._checkNotDeleted("goOffline"),wo(n._repo)}function Ou(n){n=U(n),n._checkNotDeleted("goOnline"),Fd(n._repo)}function Mu(n,e){Us(n,e)}/**
 * @license
 * Copyright 2021 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function Lu(n){jn(Is),ht(new Pe("database",(e,{instanceIdentifier:t})=>{const i=e.getProvider("app").getImmediate(),s=e.getProvider("auth-internal"),r=e.getProvider("app-check-internal");return Xi(i,s,r,t)},"PUBLIC").setMultipleInstances(!0)),me(Os,Ms,n),me(Os,Ms,"esm2020")}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */const Fu={".sv":"timestamp"};function Bu(){return Fu}function $u(n){return{".sv":{increment:n}}}/**
 * @license
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */class Lo{constructor(e,t){this.committed=e,this.snapshot=t}toJSON(){return{committed:this.committed,snapshot:this.snapshot.toJSON()}}}function Wu(n,e,t){if(n=U(n),te("Reference.transaction",n._path),n.key===".length"||n.key===".keys")throw"Reference.transaction failed: "+n.key+" is a read-only object.";const i=(t==null?void 0:t.applyLocally)??!0,s=new j,r=(a,l,c)=>{let d=null;a?s.reject(a):(d=new ke(c,new Y(n._repo,n._path),k),s.resolve(new Lo(l,d)))},o=Ao(n,()=>{});return Bd(n._repo,n._path,e,r,o,i),s.promise}/**
 * @license
 * Copyright 2017 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */Q.prototype.simpleListen=function(n,e){this.sendRequest("q",{p:n},e)},Q.prototype.echo=function(n,e){this.sendRequest("echo",{d:n},e)};const Uu=function(n){const e=Q.prototype.put;return Q.prototype.put=function(t,i,s,r){r!==void 0&&(r=n()),e.call(this,t,i,s,r)},function(){Q.prototype.put=e}},Hu=function(n){Au(n)};/**
 * @license
 * Copyright 2023 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */function zu({app:n,url:e,version:t,customAuthImpl:i,customAppCheckImpl:s,nodeAdmin:r=!1}){jn(t);const o=new Pn("database-standalone"),a=new Rn("auth-internal",o);let l;return s&&(l=new Rn("app-check-internal",o),l.setComponent(new Pe("app-check-internal",()=>s,"PRIVATE"))),a.setComponent(new Pe("auth-internal",()=>i,"PRIVATE")),Xi(n,a,l,e,r)}Lu();const Vu=Object.freeze(Object.defineProperty({__proto__:null,DataSnapshot:ke,Database:Do,OnDisconnect:To,QueryConstraint:J,TransactionResult:Lo,_QueryImpl:V,_QueryParams:Jt,_ReferenceImpl:Y,_TEST_ACCESS_forceRestClient:Hu,_TEST_ACCESS_hijackHash:Uu,_initStandalone:zu,_repoManagerDatabaseFromApp:Xi,_setSDKVersion:jn,_validatePathString:yn,_validateWritablePath:te,child:He,connectDatabaseEmulator:Mo,enableLogging:Mu,endAt:su,endBefore:ou,equalTo:Eu,forceLongPolling:Ru,forceWebSockets:Nu,get:Xd,getDatabase:Pu,goOffline:Du,goOnline:Ou,increment:$u,limitToFirst:du,limitToLast:fu,off:iu,onChildAdded:Zd,onChildChanged:eu,onChildMoved:tu,onChildRemoved:nu,onDisconnect:Gd,onValue:Ao,orderByChild:_u,orderByKey:mu,orderByPriority:vu,orderByValue:bu,push:qd,query:Su,ref:ko,refFromURL:jd,remove:Kd,runTransaction:Wu,serverTimestamp:Bu,set:Yi,setPriority:Qd,setWithPriority:Yd,startAfter:cu,startAt:au,update:Jd},Symbol.toStringTag,{value:"Module"}));ae.ApriloWidget=Tn,ae.default=ns,Object.defineProperties(ae,{__esModule:{value:!0},[Symbol.toStringTag]:{value:"Module"}})})(this.ApriloChat=this.ApriloChat||{});
