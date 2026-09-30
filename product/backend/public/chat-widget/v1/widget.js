var Nu=Object.defineProperty;var Ru=(se,re,S)=>re in se?Nu(se,re,{enumerable:!0,configurable:!0,writable:!0,value:S}):se[re]=S;var g=(se,re,S)=>Ru(se,typeof re!="symbol"?re+"":re,S);(function(se){"use strict";let re=class{constructor(){g(this,"debugMode",!1)}setDebug(e){this.debugMode=e}isDebug(){return this.debugMode}formatPrefix(e){return`[ApriloWidget:${e}]`}debug(e,t,...i){this.debugMode&&console.log(`%c${this.formatPrefix(e)}`,"color: #22C55E; font-weight: bold;",t,...i)}info(e,t,...i){console.info(`%c${this.formatPrefix(e)}`,"color: #0284C7; font-weight: bold;",t,...i)}warn(e,t,...i){console.warn(`%c${this.formatPrefix(e)}`,"color: #F59E0B; font-weight: bold;",t,...i)}error(e,t,...i){console.error(`%c${this.formatPrefix(e)}`,"color: #EF4444; font-weight: bold;",t,...i)}};const S=new re;class No{constructor(){g(this,"_isOpen",!1);g(this,"_isLoading",!1);g(this,"_isTyping",!1);g(this,"_unreadCount",0);g(this,"_config",null);g(this,"_conversation",null);g(this,"_messages",[]);g(this,"_mode","ai");g(this,"_error",null);g(this,"listeners",new Set)}subscribe(e){return this.listeners.add(e),()=>this.listeners.delete(e)}notify(e){S.debug("State",`Event: ${e}`),this.listeners.forEach(t=>{try{t(e)}catch(i){S.error("State",`Listener error for ${e}`,i)}})}get isOpen(){return this._isOpen}get isLoading(){return this._isLoading}get isTyping(){return this._isTyping}get unreadCount(){return this._unreadCount}get config(){return this._config}get conversation(){return this._conversation}get messages(){return[...this._messages]}get mode(){return this._mode}get error(){return this._error}setOpen(e){this._isOpen!==e&&(this._isOpen=e,e&&(this._unreadCount=0,this.notify("unread_change")),this.notify("open_change"))}toggleOpen(){this.setOpen(!this._isOpen)}setLoading(e){this._isLoading=e}setConfig(e){this._config=e,this.notify("config_change")}setConversation(e){this._conversation=e,e!=null&&e.mode&&(this._mode=e.mode,this.notify("mode_change")),this.notify("conversation_change")}setMessages(e){this._messages=[...e],this.notify("messages_change")}addMessage(e){this._messages.some(t=>t.id===e.id)||(this._messages.push(e),!this._isOpen&&e.sender_type!=="user"&&(this._unreadCount++,this.notify("unread_change")),this.notify("messages_change"))}setTyping(e){this._isTyping!==e&&(this._isTyping=e,this.notify("typing_change"))}setMode(e){this._mode!==e&&(this._mode=e,this.notify("mode_change"))}setError(e){this._error=e,this.notify("error_change")}reset(){this._isOpen=!1,this._isLoading=!1,this._isTyping=!1,this._unreadCount=0,this._conversation=null,this._messages=[],this._mode="ai",this._error=null,this.notify("open_change"),this.notify("messages_change")}}class Ro{constructor(e){g(this,"element");g(this,"badge");g(this,"iconContainer");this.state=e,this.element=document.createElement("button"),this.element.className="aprilo-launcher",this.element.setAttribute("type","button"),this.element.setAttribute("aria-label","Open support chat"),this.iconContainer=document.createElement("div"),this.iconContainer.className="aprilo-launcher-icon",this.renderIcon(!1),this.badge=document.createElement("span"),this.badge.className="aprilo-launcher-badge",this.badge.style.display="none",this.element.appendChild(this.iconContainer),this.element.appendChild(this.badge),this.element.addEventListener("click",()=>{this.state.toggleOpen()}),this.state.subscribe(t=>{if(t==="open_change"){const i=this.state.isOpen;this.renderIcon(i),this.element.setAttribute("aria-label",i?"Close support chat":"Open support chat")}else t==="unread_change"?this.updateBadge():t==="config_change"&&this.applyConfig()})}getElement(){return this.element}applyConfig(){const e=this.state.config;e&&(e.widget.position==="bottom-left"?this.element.classList.add("position-bottom-left"):this.element.classList.remove("position-bottom-left"),e.behavior.show_launcher?this.element.style.display="flex":this.element.style.display="none")}updateBadge(){const e=this.state.unreadCount;e>0&&!this.state.isOpen?(this.badge.textContent=e>9?"9+":String(e),this.badge.style.display="flex"):this.badge.style.display="none"}renderIcon(e){e?this.iconContainer.innerHTML=`
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      `:this.iconContainer.innerHTML=`
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
      `}}function Gi(n){if(typeof n!="string")return!1;const e=n.trim();return e?/^pk_(live|test)_[a-zA-Z0-9_\-]+$/.test(e):!1}function oe(n){return n.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;").replace(/'/g,"&#039;")}function Po(n){try{return new Date(n).toLocaleTimeString([],{hour:"2-digit",minute:"2-digit"})}catch{return""}}class Do{constructor(e){g(this,"element");g(this,"nameEl");g(this,"statusEl");g(this,"avatarEl");this.state=e,this.element=document.createElement("header"),this.element.className="aprilo-header";const t=document.createElement("div");t.className="aprilo-header-info",this.avatarEl=document.createElement("div"),this.avatarEl.className="aprilo-header-avatar";const i=document.createElement("div");i.className="aprilo-header-titles",this.nameEl=document.createElement("div"),this.nameEl.className="aprilo-header-name",this.nameEl.textContent="Aprilo Support",this.statusEl=document.createElement("div"),this.statusEl.className="aprilo-header-status",this.statusEl.innerHTML=`
      <span class="aprilo-status-dot"></span>
      <span>Online</span>
    `,i.appendChild(this.nameEl),i.appendChild(this.statusEl),t.appendChild(this.avatarEl),t.appendChild(i);const s=document.createElement("div");s.className="aprilo-header-actions";const r=document.createElement("button");r.className="aprilo-header-btn",r.setAttribute("type","button"),r.setAttribute("aria-label","Close chat"),r.innerHTML=`
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="18" y1="6" x2="6" y2="18"></line>
        <line x1="6" y1="6" x2="18" y2="18"></line>
      </svg>
    `,r.addEventListener("click",()=>{this.state.setOpen(!1)}),s.appendChild(r),this.element.appendChild(t),this.element.appendChild(s),this.state.subscribe(o=>{(o==="config_change"||o==="mode_change")&&this.render()}),this.render()}getElement(){return this.element}render(){var s,r,o,a;const e=this.state.config,t=this.state.mode,i=((s=e==null?void 0:e.ai)==null?void 0:s.display_name)||((r=e==null?void 0:e.widget)==null?void 0:r.name)||"Aprilo Assistant";this.nameEl.textContent=t==="human"?"Support Specialist":i,(o=e==null?void 0:e.widget)!=null&&o.avatar_url?this.avatarEl.innerHTML=`<img src="${oe(e.widget.avatar_url)}" alt="${oe(i)}" />`:this.avatarEl.innerHTML=`
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
      `:((a=e==null?void 0:e.support)==null?void 0:a.is_online)===!1?this.statusEl.innerHTML=`
        <span class="aprilo-status-dot" style="background: #9CA3AF; box-shadow: none;"></span>
        <span>Offline</span>
      `:this.statusEl.innerHTML=`
        <span class="aprilo-status-dot"></span>
        <span>Online</span>
      `}}class Oo{constructor(e,t){g(this,"element");this.state=e,this.onQuickAction=t,this.element=document.createElement("div"),this.element.className="aprilo-welcome-card",this.state.subscribe(i=>{i==="config_change"&&this.render()}),this.render()}getElement(){return this.element}setVisible(e){this.element.style.display=e?"block":"none"}render(){var o;const e=this.state.config;if(!e)return;const t=((o=e.organization)==null?void 0:o.name)||"Aprilo Support",i=e.widget.welcome_message||"Hi! How can we help you today?",s=e.quick_actions||[];let r="";s.length>0&&(r=`
        <div class="aprilo-quick-actions">
          ${s.map(a=>`
            <button type="button" class="aprilo-quick-btn" data-action-id="${oe(a.id)}">
              <span>${oe(a.label)}</span>
              <span class="aprilo-quick-btn-arrow">&rarr;</span>
            </button>
          `).join("")}
        </div>
      `),this.element.innerHTML=`
      <div class="aprilo-welcome-heading">Welcome to ${oe(t)}</div>
      <div class="aprilo-welcome-text">${oe(i)}</div>
      ${r}
    `,this.element.querySelectorAll(".aprilo-quick-btn").forEach(a=>{a.addEventListener("click",()=>{const l=a.getAttribute("data-action-id"),c=s.find(d=>d.id===l);c&&this.onQuickAction(c)})})}}class Mo{constructor(e){g(this,"element");this.state=e,this.element=document.createElement("div"),this.element.className="aprilo-messages-list",this.element.style.display="flex",this.element.style.flexDirection="column",this.element.style.gap="10px",this.state.subscribe(t=>{(t==="messages_change"||t==="mode_change")&&this.render()}),this.render()}getElement(){return this.element}scrollToBottom(e=!0){const t=this.element.parentElement;t&&t.scrollTo({top:t.scrollHeight,behavior:e?"smooth":"auto"})}render(){const e=this.state.messages,t=this.state.mode;let i="";e.forEach(s=>{i+=this.renderMessage(s)}),t==="human_pending"&&(i+=`
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
      `),this.element.innerHTML=i,this.scrollToBottom()}renderMessage(e){const t=e.sender_type==="agent"?"human":e.sender_type,i=Po(e.created_at),s=oe(e.content);let r="";return t==="human"&&e.sender_name?r=`<div class="aprilo-msg-meta">${oe(e.sender_name)} &bull; ${i}</div>`:t==="assistant"?r=`<div class="aprilo-msg-meta">Aprilo Support AI &bull; ${i}</div>`:i&&(r=`<div class="aprilo-msg-meta">${i}</div>`),`
      <div class="aprilo-msg ${t}">
        <div class="aprilo-msg-bubble">${s}</div>
        ${r}
      </div>
    `}}class Lo{constructor(e){g(this,"element");this.state=e,this.element=document.createElement("div"),this.element.className="aprilo-typing",this.element.style.display="none",this.element.innerHTML=`
      <div class="aprilo-typing-dot"></div>
      <div class="aprilo-typing-dot"></div>
      <div class="aprilo-typing-dot"></div>
    `,this.state.subscribe(t=>{t==="typing_change"&&(this.element.style.display=this.state.isTyping?"flex":"none")})}getElement(){return this.element}}class Fo{constructor(e){g(this,"element");this.state=e,this.element=document.createElement("div"),this.element.className="aprilo-offline-banner",this.element.style.display="none",this.state.subscribe(t=>{t==="config_change"&&this.render()}),this.render()}getElement(){return this.element}render(){const e=this.state.config;if(!e)return;if(e.support.is_online===!1){const i=e.widget.offline_message||"Our team is currently offline. Please leave your message and we will respond as soon as we return.";this.element.innerHTML=`
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; margin-top: 1px;">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span>${oe(i)}</span>
      `,this.element.style.display="flex"}else this.element.style.display="none"}}class Bo{constructor(e,t){g(this,"element");g(this,"inputEl");g(this,"sendBtn");g(this,"brandingEl");this.state=e,this.onSend=t,this.element=document.createElement("footer"),this.element.className="aprilo-footer";const i=document.createElement("div");i.className="aprilo-input-row",this.inputEl=document.createElement("input"),this.inputEl.className="aprilo-input",this.inputEl.type="text",this.inputEl.placeholder="Type your message...",this.inputEl.setAttribute("aria-label","Type your message"),this.sendBtn=document.createElement("button"),this.sendBtn.className="aprilo-send-btn",this.sendBtn.setAttribute("type","button"),this.sendBtn.setAttribute("aria-label","Send message"),this.sendBtn.disabled=!0,this.sendBtn.innerHTML=`
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="22" y1="2" x2="11" y2="13"></line>
        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
      </svg>
    `,i.appendChild(this.inputEl),i.appendChild(this.sendBtn),this.brandingEl=document.createElement("div"),this.brandingEl.className="aprilo-branding",this.brandingEl.innerHTML='Powered by <a href="https://apriloinfotech.com" target="_blank" rel="noopener noreferrer">Aprilo Support</a>',this.element.appendChild(i),this.element.appendChild(this.brandingEl),this.inputEl.addEventListener("input",()=>{this.sendBtn.disabled=!this.inputEl.value.trim()}),this.inputEl.addEventListener("keydown",s=>{s.key==="Enter"&&!s.shiftKey&&(s.preventDefault(),this.handleSend())}),this.sendBtn.addEventListener("click",()=>{this.handleSend()}),this.state.subscribe(s=>{var r,o;if(s==="config_change"){const a=((o=(r=this.state.config)==null?void 0:r.behavior)==null?void 0:o.show_branding)??!0;this.brandingEl.style.display=a?"block":"none"}})}getElement(){return this.element}focus(){setTimeout(()=>this.inputEl.focus(),150)}handleSend(){const e=this.inputEl.value.trim();e&&(this.inputEl.value="",this.sendBtn.disabled=!0,this.onSend(e))}}class Wo{constructor(e,t){g(this,"element");g(this,"header");g(this,"welcome");g(this,"messages");g(this,"typing");g(this,"offline");g(this,"input");g(this,"body");this.state=e,this.element=document.createElement("div"),this.element.className="aprilo-panel",this.element.setAttribute("role","dialog"),this.element.setAttribute("aria-modal","true"),this.element.setAttribute("aria-label","Support Chat Window"),this.header=new Do(this.state),this.element.appendChild(this.header.getElement()),this.body=document.createElement("div"),this.body.className="aprilo-body",this.body.setAttribute("role","region"),this.body.setAttribute("aria-live","polite"),this.welcome=new Oo(this.state,i=>t.onQuickAction(i)),this.messages=new Mo(this.state),this.typing=new Lo(this.state),this.offline=new Fo(this.state),this.body.appendChild(this.welcome.getElement()),this.body.appendChild(this.offline.getElement()),this.body.appendChild(this.messages.getElement()),this.body.appendChild(this.typing.getElement()),this.element.appendChild(this.body),this.input=new Bo(this.state,i=>t.onSend(i)),this.element.appendChild(this.input.getElement()),this.element.addEventListener("keydown",i=>{i.key==="Escape"&&this.state.isOpen&&(i.stopPropagation(),this.state.setOpen(!1))}),this.state.subscribe(i=>{i==="open_change"?this.state.isOpen?(this.element.classList.add("open"),this.input.focus(),this.messages.scrollToBottom()):this.element.classList.remove("open"):i==="config_change"?this.applyConfig():i==="messages_change"&&this.state.messages.length>0&&this.welcome.setVisible(!1)})}getElement(){return this.element}applyConfig(){const e=this.state.config;e&&(e.widget.position==="bottom-left"?this.element.classList.add("position-bottom-left"):this.element.classList.remove("position-bottom-left"))}}const ae={widget:{id:"WIDGET_DEFAULT",name:"Aprilo Assistant",display_name:"Chat with Aprilo Support",avatar_url:"",welcome_message:"Hi! How can we assist you with Aprilo Support today?",offline_message:"Our team is currently offline. Please leave your message and we will respond shortly.",position:"bottom-right",enabled:!0},theme:{primary_color:"#22C55E",secondary_color:"#FF8A00",background_color:"#F8FAFC",text_color:"#1F2937",border_radius:"16px"},behavior:{auto_open:!1,show_launcher:!0,show_branding:!0,human_support_enabled:!0},ai:{enabled:!0,agent_id:"AI_SUPPORT_01",display_name:"Aprilo Support AI"},support:{enabled:!0,business_hours_enabled:!1,is_online:!0},quick_actions:[{id:"magento",label:"Magento 2 Solutions",message:"Tell me about your Magento 2 AI and checkout solutions."},{id:"support_trial",label:"1-Month Free Trial*",message:"How does the 1-Month Free Trial for Aprilo Support AI work?"},{id:"agent",label:"Talk to Support Agent",message:"I would like to talk directly with a live support agent."}],organization:{id:"01a0ca0a-90e0-7257-a23c-4d5c723aff99",name:"Aprilo Infotech"},firebase:{apiKey:"AIzaSyDU5Ce2X5w35sZH81e5nX9i41xXj6YXoMg",databaseURL:"https://aprilo-infotech-default-rtdb.firebaseio.com",projectId:"aprilo-infotech",appId:"1:113916326733:web:5659980b28225e34b6fd04"}};async function Uo(n,e){const t=`${n.replace(/\/+$/,"")}/api/widget/config?widget_key=${encodeURIComponent(e)}`;S.debug("API",`Fetching configuration from ${t}`);try{const i=new AbortController,s=setTimeout(()=>i.abort(),8e3),r=await fetch(t,{method:"GET",headers:{Accept:"application/json"},signal:i.signal});if(clearTimeout(s),r.ok){const o=await r.json();if(o.success&&o.widget&&o.theme)return S.info("API","Configuration loaded successfully from backend"),{widget:{...ae.widget,...o.widget},theme:{...ae.theme,...o.theme},behavior:{...ae.behavior,...o.behavior},ai:{...ae.ai,...o.ai},support:{...ae.support,...o.support},quick_actions:o.quick_actions||ae.quick_actions,organization:o.organization||ae.organization,firebase:o.firebase||ae.firebase}}S.warn("API",`Backend returned status ${r.status}. Using tenant fallback configuration.`)}catch(i){S.warn("API","Failed to reach backend configuration endpoint. Employing default resilient configuration.",i)}return{...ae,organization:{id:"01a0ca0a-90e0-7257-a23c-4d5c723aff99",name:e.includes("demo")?"Demo Corporation":"Aprilo Infotech"}}}async function $o(n,e,t){const i=`${n.replace(/\/+$/,"")}/api/widget/conversations`;S.debug("API",`Creating conversation at ${i}`);try{const o=new AbortController,a=setTimeout(()=>o.abort(),8e3),l=await fetch(i,{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify({widget_key:e,session_id:t,domain:window.location.hostname,page_url:window.location.href,page_title:document.title}),signal:o.signal});if(clearTimeout(a),l.ok){const c=await l.json();if(c.success&&c.conversation_id)return{id:c.conversation_id,customer_id:c.customer_id,session_id:t,mode:c.mode||"ai",status:"open",created_at:new Date().toISOString()}}}catch(o){S.warn("API","Backend conversation creation failed. Initializing local resilient session.",o)}const s=`CONV_${Date.now().toString(36).toUpperCase()}`,r=`CUS_${Math.random().toString(36).substring(2,8).toUpperCase()}`;return{id:s,customer_id:r,session_id:t,mode:"ai",status:"open",created_at:new Date().toISOString()}}async function Ho(n,e,t){const i=`${n.replace(/\/+$/,"")}/api/widget/conversations/${e}/messages`;S.debug("API",`Sending message to ${i}`,t);try{const s=new AbortController,r=setTimeout(()=>s.abort(),1e4),o=await fetch(i,{method:"POST",headers:{"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify({message:t.message,message_type:t.message_type||"text",metadata:t.metadata||{}}),signal:s.signal});if(clearTimeout(r),o.ok)return await o.json()}catch(s){S.warn("API","Backend message send call failed. Using offline simulation ID.",s)}return{success:!0,message_id:`MSG_${Date.now().toString(36).toUpperCase()}`}}class Vo{constructor(){g(this,"isConnected",!1);g(this,"unsubscribeMessages");g(this,"unsubscribeConversation")}async connect(e){if(!this.isConnected){if(!e.firebaseConfig||!e.firebaseConfig.databaseURL){S.debug("Firebase","No Firebase configuration provided for tenant. Realtime sync skipped.");return}try{S.debug("Firebase",`Initializing Realtime Database for tenant ${e.organizationId}`);const{initializeApp:t,getApps:i}=await Promise.resolve().then(()=>Dl),{getDatabase:s,ref:r,onChildAdded:o,onValue:a,off:l}=await Promise.resolve().then(()=>Au),c=i().find(p=>p.name==="aprilo-widget")||t(e.firebaseConfig,"aprilo-widget"),d=s(c),h=r(d,`tenants/${e.organizationId}/messages/${e.conversationId}`);o(h,p=>{const _=p.val();_&&e.onMessage({id:p.key||`MSG_${Date.now()}`,conversation_id:e.conversationId,sender_type:_.sender_type||"assistant",message_type:_.message_type||"text",content:_.content||_.message||"",sender_name:_.sender_name,created_at:_.created_at||new Date().toISOString(),status:"delivered"})});const u=r(d,`tenants/${e.organizationId}/conversations/${e.conversationId}`);a(u,p=>{const _=p.val();_&&(_.mode&&e.onModeChange(_.mode),typeof _.is_typing=="boolean"&&e.onTyping(_.is_typing))}),this.unsubscribeMessages=()=>l(h),this.unsubscribeConversation=()=>l(u),this.isConnected=!0,S.info("Firebase","Connected to Realtime Database successfully")}catch(t){S.warn("Firebase","Unable to establish Firebase Realtime connection. Message polling/HTTP fallback active.",t)}}}disconnect(){this.unsubscribeMessages&&(this.unsubscribeMessages(),this.unsubscribeMessages=void 0),this.unsubscribeConversation&&(this.unsubscribeConversation(),this.unsubscribeConversation=void 0),this.isConnected=!1}}class zo{constructor(){g(this,"memoryStore",new Map);g(this,"isStorageAvailable",!0);try{const e="__aprilo_storage_test__";window.localStorage.setItem(e,"1"),window.localStorage.removeItem(e)}catch(e){this.isStorageAvailable=!1,S.warn("Core","localStorage unavailable or blocked. Falling back to memory storage.",e)}}getItem(e){if(this.isStorageAvailable)try{return window.localStorage.getItem(e)}catch{return this.memoryStore.get(e)||null}return this.memoryStore.get(e)||null}setItem(e,t){if(this.isStorageAvailable)try{window.localStorage.setItem(e,t);return}catch{}this.memoryStore.set(e,t)}removeItem(e){if(this.isStorageAvailable)try{window.localStorage.removeItem(e)}catch{}this.memoryStore.delete(e)}getOrCreateSessionId(e){const t=`aprilo_session_${e}`;let i=this.getItem(t);return i||(i=`sess_${Date.now()}_${Math.random().toString(36).substring(2,9)}`,this.setItem(t,i)),i}getConversationId(e){return this.getItem(`aprilo_conv_${e}`)}setConversationId(e,t){this.setItem(`aprilo_conv_${e}`,t)}}const qi=new zo,jo=':host{all:initial;--aprilo-primary: #22C55E;--aprilo-primary-dark: #16A34A;--aprilo-primary-deep: #065F46;--aprilo-accent: #FF8A00;--aprilo-accent-light: #FFB020;--aprilo-background: #F8FAFC;--aprilo-panel-bg: #FFFFFF;--aprilo-text: #1F2937;--aprilo-secondary-text: #6B7280;--aprilo-border: #E2E8F0;--aprilo-border-radius: 16px;--aprilo-shadow: 0 12px 36px -4px rgba(0, 0, 0, .15), 0 4px 16px -2px rgba(0, 0, 0, .08);--aprilo-font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";--aprilo-z-index: 2147483640;font-family:var(--aprilo-font-family);font-size:14px;line-height:1.5;color:var(--aprilo-text);box-sizing:border-box;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}*,*:before,*:after{box-sizing:border-box;margin:0;padding:0}.aprilo-launcher{position:fixed;bottom:24px;right:24px;width:60px;height:60px;border-radius:50%;background:var(--aprilo-primary);color:#fff;box-shadow:0 8px 24px #22c55e59,0 2px 8px #0000001a;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:var(--aprilo-z-index);transition:transform .22s cubic-bezier(.34,1.56,.64,1),box-shadow .2s ease,background-color .2s ease;outline:none;touch-action:manipulation}.aprilo-launcher:hover{transform:scale(1.06);background:var(--aprilo-primary-dark);box-shadow:0 10px 28px #16a34a6b,0 4px 12px #0000001f}.aprilo-launcher:focus-visible{box-shadow:0 0 0 3px #22c55e66,0 8px 24px #0003}.aprilo-launcher:active{transform:scale(.96)}.aprilo-launcher.position-bottom-left{right:auto;left:24px}.aprilo-launcher-icon{width:30px;height:30px;display:flex;align-items:center;justify-content:center;transition:transform .25s ease,opacity .2s ease}.aprilo-launcher-badge{position:absolute;top:-2px;right:-2px;min-width:20px;height:20px;padding:0 6px;border-radius:10px;background:var(--aprilo-accent);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;border:2px solid #FFFFFF;box-shadow:0 2px 6px #ff8a0066;animation:aprilo-pulse 2s infinite}@keyframes aprilo-pulse{0%{transform:scale(1)}50%{transform:scale(1.1)}to{transform:scale(1)}}.aprilo-panel{position:fixed;bottom:96px;right:24px;width:380px;max-width:calc(100vw - 32px);height:620px;max-height:calc(100vh - 120px);background:var(--aprilo-panel-bg);border-radius:var(--aprilo-border-radius);box-shadow:var(--aprilo-shadow);display:flex;flex-direction:column;overflow:hidden;z-index:var(--aprilo-z-index);border:1px solid rgba(0,0,0,.08);opacity:0;visibility:hidden;transform:translateY(18px) scale(.96);transform-origin:bottom right;transition:opacity .24s cubic-bezier(.16,1,.3,1),transform .24s cubic-bezier(.16,1,.3,1),visibility .24s}.aprilo-panel.position-bottom-left{right:auto;left:24px;transform-origin:bottom left}.aprilo-panel.open{opacity:1;visibility:visible;transform:translateY(0) scale(1)}.aprilo-header{background:linear-gradient(135deg,var(--aprilo-primary),var(--aprilo-primary-dark));color:#fff;padding:16px 18px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,.1);flex-shrink:0}.aprilo-header-info{display:flex;align-items:center;gap:12px}.aprilo-header-avatar{width:40px;height:40px;border-radius:50%;background:#fff3;border:2px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}.aprilo-header-avatar img{width:100%;height:100%;object-fit:cover}.aprilo-header-avatar svg{width:22px;height:22px;fill:#fff}.aprilo-header-titles{display:flex;flex-direction:column}.aprilo-header-name{font-size:15px;font-weight:600;letter-spacing:-.01em;color:#fff}.aprilo-header-status{display:flex;align-items:center;gap:6px;font-size:12px;color:#ffffffe6}.aprilo-status-dot{width:8px;height:8px;border-radius:50%;background:#a7f3d0;box-shadow:0 0 8px #34d399}.aprilo-header-actions{display:flex;align-items:center;gap:6px}.aprilo-header-btn{background:#ffffff26;border:none;color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s ease,transform .15s ease;outline:none}.aprilo-header-btn:hover{background:#ffffff47;transform:scale(1.05)}.aprilo-header-btn:focus-visible{box-shadow:0 0 0 2px #fff}.aprilo-body{flex:1;background:var(--aprilo-background);overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px;scroll-behavior:smooth}.aprilo-welcome-card{background:#fff;border-radius:12px;padding:16px;box-shadow:0 2px 8px #0000000a;border:1px solid var(--aprilo-border);margin-bottom:8px}.aprilo-welcome-heading{font-size:15px;font-weight:600;color:var(--aprilo-text);margin-bottom:6px}.aprilo-welcome-text{font-size:13px;color:var(--aprilo-secondary-text);line-height:1.5}.aprilo-quick-actions{display:flex;flex-direction:column;gap:8px;margin-top:12px}.aprilo-quick-btn{background:#fff;border:1px solid var(--aprilo-border);border-radius:8px;padding:10px 14px;font-size:13px;font-weight:500;color:var(--aprilo-text);text-align:left;cursor:pointer;transition:all .18s ease;display:flex;align-items:center;justify-content:space-between}.aprilo-quick-btn:hover{background:#f0fdf4;border-color:var(--aprilo-primary);color:var(--aprilo-primary-dark);transform:translate(2px)}.aprilo-quick-btn-arrow{color:var(--aprilo-secondary-text);font-size:14px;transition:transform .15s ease}.aprilo-quick-btn:hover .aprilo-quick-btn-arrow{transform:translate(3px);color:var(--aprilo-primary)}.aprilo-msg{display:flex;flex-direction:column;max-width:82%;word-break:break-word;animation:aprilo-fade-in .2s ease-out}@keyframes aprilo-fade-in{0%{opacity:0;transform:translateY(6px)}to{opacity:1;transform:translateY(0)}}.aprilo-msg.user,.aprilo-msg.customer{align-self:flex-end;align-items:flex-end}.aprilo-msg.assistant,.aprilo-msg.human{align-self:flex-start;align-items:flex-start}.aprilo-msg-bubble{padding:10px 14px;border-radius:14px;font-size:13.5px;line-height:1.45}.aprilo-msg.user .aprilo-msg-bubble,.aprilo-msg.customer .aprilo-msg-bubble{background:var(--aprilo-primary);color:#fff;border-bottom-right-radius:4px}.aprilo-msg.assistant .aprilo-msg-bubble{background:#fff;color:var(--aprilo-text);border:1px solid var(--aprilo-border);border-bottom-left-radius:4px;box-shadow:0 1px 3px #0000000a}.aprilo-msg.human .aprilo-msg-bubble{background:#eff6ff;color:#1e3a8a;border:1px solid #BFDBFE;border-bottom-left-radius:4px}.aprilo-msg.system{align-self:center;align-items:center;max-width:90%;margin:6px 0}.aprilo-msg.system .aprilo-msg-bubble{background:#fef3c7;color:#92400e;border:1px solid #FDE68A;font-size:12px;padding:6px 12px;border-radius:20px;text-align:center}.aprilo-msg-meta{font-size:10.5px;color:var(--aprilo-secondary-text);margin-top:4px;padding:0 4px}.aprilo-typing{display:flex;align-items:center;gap:4px;padding:10px 14px;background:#fff;border-radius:14px 14px 14px 4px;border:1px solid var(--aprilo-border);width:fit-content;align-self:flex-start}.aprilo-typing-dot{width:6px;height:6px;border-radius:50%;background:var(--aprilo-secondary-text);animation:aprilo-bounce 1.3s infinite ease-in-out}.aprilo-typing-dot:nth-child(1){animation-delay:0s}.aprilo-typing-dot:nth-child(2){animation-delay:.2s}.aprilo-typing-dot:nth-child(3){animation-delay:.4s}@keyframes aprilo-bounce{0%,80%,to{transform:scale(.6);opacity:.4}40%{transform:scale(1.1);opacity:1}}.aprilo-offline-banner{background:#fffbeb;border:1px solid #FDE68A;border-radius:10px;padding:12px;font-size:12.5px;color:#92400e;display:flex;align-items:flex-start;gap:8px;margin-top:8px}.aprilo-footer{background:#fff;padding:12px 14px;border-top:1px solid var(--aprilo-border);display:flex;flex-direction:column;gap:6px;flex-shrink:0}.aprilo-input-row{display:flex;align-items:center;gap:8px}.aprilo-input{flex:1;background:var(--aprilo-background);border:1px solid var(--aprilo-border);border-radius:20px;padding:10px 16px;font-family:inherit;font-size:13.5px;color:var(--aprilo-text);outline:none;transition:border-color .15s ease,box-shadow .15s ease;resize:none}.aprilo-input:focus{border-color:var(--aprilo-primary);box-shadow:0 0 0 2px #22c55e26;background:#fff}.aprilo-input::placeholder{color:#9ca3af}.aprilo-send-btn{width:38px;height:38px;border-radius:50%;background:var(--aprilo-primary);border:none;color:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:transform .15s ease,background-color .15s ease,opacity .15s ease;flex-shrink:0;outline:none}.aprilo-send-btn:hover:not(:disabled){background:var(--aprilo-primary-dark);transform:scale(1.05)}.aprilo-send-btn:disabled{opacity:.45;cursor:not-allowed}.aprilo-branding{text-align:center;font-size:11px;color:#9ca3af;letter-spacing:.01em}.aprilo-branding a{color:var(--aprilo-secondary-text);text-decoration:none;font-weight:500}.aprilo-branding a:hover{color:var(--aprilo-primary)}@media(max-width:640px){.aprilo-panel{top:0;left:0;right:0;bottom:0;width:100vw;max-width:100vw;height:100vh;height:100dvh;max-height:100dvh;border-radius:0;border:none;box-shadow:none;transform:translateY(100%)}.aprilo-panel.open{transform:translateY(0)}.aprilo-header{padding-top:max(16px,env(safe-area-inset-top))}.aprilo-footer{padding-bottom:max(12px,env(safe-area-inset-bottom))}.aprilo-launcher{bottom:max(20px,env(safe-area-inset-bottom));right:max(20px,env(safe-area-inset-right))}.aprilo-launcher.position-bottom-left{left:max(20px,env(safe-area-inset-left))}}';class vn{constructor(e){g(this,"host");g(this,"shadowRoot");g(this,"state");g(this,"launcher");g(this,"panel");g(this,"realtime");g(this,"options");g(this,"isDestroyed",!1);g(this,"initPromise");this.options={apiUrl:e.apiUrl||"https://live.apriloinfotech.com",autoOpen:e.autoOpen??!1,debug:e.debug??!1,...e},S.setDebug(!!this.options.debug),S.info("Core",`Initializing Aprilo Chat Widget for key: ${this.options.widgetKey}`),this.state=new No,this.realtime=new Vo,this.host=document.createElement("div"),this.host.id="aprilo-chat-widget-root",this.shadowRoot=this.host.attachShadow({mode:"open"});const t=document.createElement("style");t.textContent=jo,this.shadowRoot.appendChild(t),this.launcher=new Ro(this.state),this.panel=new Wo(this.state,{onSend:i=>this.handleSendMessage(i),onQuickAction:i=>this.handleQuickAction(i)}),this.shadowRoot.appendChild(this.launcher.getElement()),this.shadowRoot.appendChild(this.panel.getElement()),document.body?document.body.appendChild(this.host):document.addEventListener("DOMContentLoaded",()=>{document.body.appendChild(this.host)}),this.state.subscribe(i=>{i==="open_change"&&this.state.isOpen&&this.ensureConversationActive()}),this.initPromise=this.loadConfiguration()}async loadConfiguration(){try{const e=await Uo(this.options.apiUrl,this.options.widgetKey);this.state.setConfig(e),this.applyTheme(e.theme),(this.options.autoOpen||e.behavior.auto_open)&&setTimeout(()=>{this.isDestroyed||this.state.setOpen(!0)},1200)}catch(e){S.error("Core","Failed to load configuration:",e)}}applyTheme(e){e&&(e.primary_color&&(this.host.style.setProperty("--aprilo-primary",e.primary_color),this.host.style.setProperty("--aprilo-primary-dark",this.adjustColorBrightness(e.primary_color,-18))),e.secondary_color&&this.host.style.setProperty("--aprilo-accent",e.secondary_color),e.background_color&&this.host.style.setProperty("--aprilo-background",e.background_color),e.text_color&&this.host.style.setProperty("--aprilo-text",e.text_color),e.border_radius&&this.host.style.setProperty("--aprilo-border-radius",e.border_radius))}async ensureConversationActive(){var s;if(this.state.conversation)return;const e=qi.getOrCreateSessionId(this.options.widgetKey),t=await $o(this.options.apiUrl,this.options.widgetKey,e);this.state.setConversation(t),qi.setConversationId(this.options.widgetKey,t.id);const i=this.state.config;(s=i==null?void 0:i.organization)!=null&&s.id&&this.realtime.connect({organizationId:i.organization.id,conversationId:t.id,firebaseConfig:i.firebase,onMessage:r=>{this.state.addMessage(r),(r.sender_type==="human"||r.sender_type==="agent")&&this.state.setMode("human")},onTyping:r=>this.state.setTyping(r),onModeChange:r=>this.state.setMode(r)})}async handleSendMessage(e){await this.ensureConversationActive();const t=this.state.conversation;if(!t)return;const i={id:`usr_${Date.now()}_${Math.random().toString(36).substring(2,6)}`,conversation_id:t.id,sender_type:"user",message_type:"text",content:e,created_at:new Date().toISOString(),status:"sending"};this.state.addMessage(i),this.state.setTyping(!0);try{const s=await Ho(this.options.apiUrl,t.id,{message:e,message_type:"text"});this.state.setTyping(!1),s!=null&&s.mode&&this.state.setMode(s.mode),s!=null&&s.reply?this.state.addMessage({id:s.reply.id||`bot_${Date.now()}`,conversation_id:t.id,sender_type:s.reply.sender==="user"?"user":s.reply.sender==="assistant"?"assistant":"human",message_type:"text",content:s.reply.content,sender_name:s.reply.sender_name||"Aprilo Support AI",created_at:s.reply.timestamp||new Date().toISOString(),status:"delivered"}):(this.options.widgetKey.includes("test")||this.options.widgetKey.includes("demo"))&&setTimeout(()=>{this.simulateAssistantReply(e)},500)}catch(s){S.error("Core","Error sending message:",s),this.state.setTyping(!1)}}handleQuickAction(e){this.handleSendMessage(e.message)}simulateAssistantReply(e){var a,l,c,d,h,u,p,_,C;const t=((l=(a=this.state.config)==null?void 0:a.organization)==null?void 0:l.name)||"our organization",i=((d=(c=this.state.config)==null?void 0:c.ai)==null?void 0:d.display_name)||((u=(h=this.state.config)==null?void 0:h.widget)==null?void 0:u.name)||`${t} Assistant`,s=e.toLowerCase();let r=`Thank you for reaching out to ${t}! How can we assist you today?`;s.includes("hi")||s.includes("hello")||s.includes("hey")?r=`Hello! I am ${i}, assistant for ${t}. How can I assist you with our services today?`:s.includes("service")||s.includes("what")||s.includes("offer")?r=`${t} provides professional services and solutions. Feel free to ask about our offerings or request support.`:s.includes("sales")||s.includes("pricing")||s.includes("contact")?r=`I would be happy to connect you with ${t} support. Would you like to leave a message or speak with our team?`:(s.includes("human")||s.includes("agent"))&&(this.state.setMode("human"),r=`I am routing this conversation to ${t} support. A specialist will assist you shortly.`);const o={id:`bot_${Date.now()}`,conversation_id:((p=this.state.conversation)==null?void 0:p.id)||"demo_conv",sender_type:this.state.mode==="human"?"human":"assistant",message_type:"text",content:r,sender_name:this.state.mode==="human"?"Support Agent (Sarah)":((C=(_=this.state.config)==null?void 0:_.ai)==null?void 0:C.display_name)||"Aprilo Support AI",created_at:new Date().toISOString(),status:"delivered"};this.state.addMessage(o)}adjustColorBrightness(e,t){const i=parseInt(e.replace("#",""),16),s=Math.round(2.55*t),r=(i>>16)+s,o=(i>>8&255)+s,a=(i&255)+s;return"#"+(16777216+(r<255?r<1?0:r:255)*65536+(o<255?o<1?0:o:255)*256+(a<255?a<1?0:a:255)).toString(16).slice(1)}open(){this.state.setOpen(!0)}close(){this.state.setOpen(!1)}toggle(){this.state.toggleOpen()}isOpen(){return this.state.isOpen}ready(){return this.initPromise}destroy(){this.isDestroyed=!0,this.realtime.disconnect(),this.state.reset(),this.host.parentElement&&this.host.parentElement.removeChild(this.host),S.info("Core","Aprilo Chat Widget destroyed")}}let R=null;function Ki(){try{const n=document.currentScript||document.querySelector("script[data-widget-key]");if(!n){S.debug("Core","No script with data-widget-key found during auto-init.");return}const e=n.getAttribute("data-widget-key");if(!e||!Gi(e)){S.warn("Core",`Invalid or missing data-widget-key on script tag: "${e}".`);return}const t=n.getAttribute("data-auto-open")==="true";let i=n.getAttribute("data-api-url")||void 0;if(i&&i.includes("localhost")&&typeof window<"u"&&window.location.hostname&&!window.location.hostname.includes("localhost")&&(window.location.hostname.endsWith("apriloinfotech.com")?i=window.location.origin:i="https://app.apriloinfotech.com"),!i&&n.src)try{i=new URL(n.src).origin}catch{i="https://aprilo-infotech.web.app"}const s=n.getAttribute("data-debug")==="true";R=new vn({widgetKey:e,autoOpen:t,apiUrl:i||"https://aprilo-infotech.web.app",debug:s})}catch(n){S.error("Core","Auto-initialization exception:",n)}}const Qi={version:"1.0.0",open:()=>R==null?void 0:R.open(),close:()=>R==null?void 0:R.close(),toggle:()=>R==null?void 0:R.toggle(),isOpen:()=>!!(R!=null&&R.isOpen()),destroy:()=>{R==null||R.destroy(),R=null},init:n=>!n.widgetKey||!Gi(n.widgetKey)?(S.error("Core",`Cannot initialize widget: invalid key "${n.widgetKey}"`),null):(R&&R.destroy(),R=new vn(n),R)};typeof window<"u"&&(window.ApriloChat=Qi,document.readyState==="loading"?document.addEventListener("DOMContentLoaded",Ki):Ki());const Go=()=>{};/**
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
 */const Yi={NODE_ADMIN:!1,SDK_VERSION:"${JSCORE_VERSION}"};/**
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
 */const f=function(n,e){if(!n)throw Be(e)},Be=function(n){return new Error("Firebase Database ("+Yi.SDK_VERSION+") INTERNAL ASSERT FAILED: "+n)};/**
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
 */const Ji=function(n){const e=[];let t=0;for(let i=0;i<n.length;i++){let s=n.charCodeAt(i);s<128?e[t++]=s:s<2048?(e[t++]=s>>6|192,e[t++]=s&63|128):(s&64512)===55296&&i+1<n.length&&(n.charCodeAt(i+1)&64512)===56320?(s=65536+((s&1023)<<10)+(n.charCodeAt(++i)&1023),e[t++]=s>>18|240,e[t++]=s>>12&63|128,e[t++]=s>>6&63|128,e[t++]=s&63|128):(e[t++]=s>>12|224,e[t++]=s>>6&63|128,e[t++]=s&63|128)}return e},qo=function(n){const e=[];let t=0,i=0;for(;t<n.length;){const s=n[t++];if(s<128)e[i++]=String.fromCharCode(s);else if(s>191&&s<224){const r=n[t++];e[i++]=String.fromCharCode((s&31)<<6|r&63)}else if(s>239&&s<365){const r=n[t++],o=n[t++],a=n[t++],l=((s&7)<<18|(r&63)<<12|(o&63)<<6|a&63)-65536;e[i++]=String.fromCharCode(55296+(l>>10)),e[i++]=String.fromCharCode(56320+(l&1023))}else{const r=n[t++],o=n[t++];e[i++]=String.fromCharCode((s&15)<<12|(r&63)<<6|o&63)}}return e.join("")},bn={byteToCharMap_:null,charToByteMap_:null,byteToCharMapWebSafe_:null,charToByteMapWebSafe_:null,ENCODED_VALS_BASE:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",get ENCODED_VALS(){return this.ENCODED_VALS_BASE+"+/="},get ENCODED_VALS_WEBSAFE(){return this.ENCODED_VALS_BASE+"-_."},HAS_NATIVE_SUPPORT:typeof atob=="function",encodeByteArray(n,e){if(!Array.isArray(n))throw Error("encodeByteArray takes an array as a parameter");this.init_();const t=e?this.byteToCharMapWebSafe_:this.byteToCharMap_,i=[];for(let s=0;s<n.length;s+=3){const r=n[s],o=s+1<n.length,a=o?n[s+1]:0,l=s+2<n.length,c=l?n[s+2]:0,d=r>>2,h=(r&3)<<4|a>>4;let u=(a&15)<<2|c>>6,p=c&63;l||(p=64,o||(u=64)),i.push(t[d],t[h],t[u],t[p])}return i.join("")},encodeString(n,e){return this.HAS_NATIVE_SUPPORT&&!e?btoa(n):this.encodeByteArray(Ji(n),e)},decodeString(n,e){return this.HAS_NATIVE_SUPPORT&&!e?atob(n):qo(this.decodeStringToByteArray(n,e))},decodeStringToByteArray(n,e){this.init_();const t=e?this.charToByteMapWebSafe_:this.charToByteMap_,i=[];for(let s=0;s<n.length;){const r=t[n.charAt(s++)],a=s<n.length?t[n.charAt(s)]:0;++s;const c=s<n.length?t[n.charAt(s)]:64;++s;const h=s<n.length?t[n.charAt(s)]:64;if(++s,r==null||a==null||c==null||h==null)throw new Ko;const u=r<<2|a>>4;if(i.push(u),c!==64){const p=a<<4&240|c>>2;if(i.push(p),h!==64){const _=c<<6&192|h;i.push(_)}}}return i},init_(){if(!this.byteToCharMap_){this.byteToCharMap_={},this.charToByteMap_={},this.byteToCharMapWebSafe_={},this.charToByteMapWebSafe_={};for(let n=0;n<this.ENCODED_VALS.length;n++)this.byteToCharMap_[n]=this.ENCODED_VALS.charAt(n),this.charToByteMap_[this.byteToCharMap_[n]]=n,this.byteToCharMapWebSafe_[n]=this.ENCODED_VALS_WEBSAFE.charAt(n),this.charToByteMapWebSafe_[this.byteToCharMapWebSafe_[n]]=n,n>=this.ENCODED_VALS_BASE.length&&(this.charToByteMap_[this.ENCODED_VALS_WEBSAFE.charAt(n)]=n,this.charToByteMapWebSafe_[this.ENCODED_VALS.charAt(n)]=n)}}};class Ko extends Error{constructor(){super(...arguments),this.name="DecodeBase64StringError"}}const Xi=function(n){const e=Ji(n);return bn.encodeByteArray(e,!0)},Nt=function(n){return Xi(n).replace(/\./g,"")},Rt=function(n){try{return bn.decodeString(n,!0)}catch(e){console.error("base64Decode failed: ",e)}return null};/**
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
 */function Qo(n){return Zi(void 0,n)}function Zi(n,e){if(!(e instanceof Object))return e;switch(e.constructor){case Date:const t=e;return new Date(t.getTime());case Object:n===void 0&&(n={});break;case Array:n=[];break;default:return e}for(const t in e)!e.hasOwnProperty(t)||!Yo(t)||(n[t]=Zi(n[t],e[t]));return n}function Yo(n){return n!=="__proto__"}/**
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
 */function Jo(){if(typeof self<"u")return self;if(typeof window<"u")return window;if(typeof global<"u")return global;throw new Error("Unable to locate global object.")}/**
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
 */const Xo=()=>Jo().__FIREBASE_DEFAULTS__,Zo=()=>{if(typeof process>"u"||typeof process.env>"u")return;const n=process.env.__FIREBASE_DEFAULTS__;if(n)return JSON.parse(n)},ea=()=>{if(typeof document>"u")return;let n;try{n=document.cookie.match(/__FIREBASE_DEFAULTS__=([^;]+)/)}catch{return}const e=n&&Rt(n[1]);return e&&JSON.parse(e)},es=()=>{try{return Go()||Xo()||Zo()||ea()}catch(n){console.info(`Unable to get __FIREBASE_DEFAULTS__ due to: ${n}`);return}},ta=n=>{var e,t;return(t=(e=es())==null?void 0:e.emulatorHosts)==null?void 0:t[n]},na=n=>{const e=ta(n);if(!e)return;const t=e.lastIndexOf(":");if(t<=0||t+1===e.length)throw new Error(`Invalid host ${e} with no separate hostname and port!`);const i=parseInt(e.substring(t+1),10);return e[0]==="["?[e.substring(1,t-1),i]:[e.substring(0,t),i]},wn=()=>{var n;return(n=es())==null?void 0:n.config};/**
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
 */class z{constructor(){this.reject=()=>{},this.resolve=()=>{},this.promise=new Promise((e,t)=>{this.resolve=e,this.reject=t})}wrapCallback(e){return(t,i)=>{t?this.reject(t):this.resolve(i),typeof e=="function"&&(this.promise.catch(()=>{}),e.length===1?e(t):e(t,i))}}}/**
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
 */function ia(n,e){if(n.uid)throw new Error('The "uid" field is no longer supported by mockUserToken. Please use "sub" instead for Firebase Auth User ID.');const t={alg:"none",type:"JWT"},i=e||"demo-project",s=n.iat||0,r=n.sub||n.user_id;if(!r)throw new Error("mockUserToken must contain 'sub' or 'user_id' field!");const o={iss:`https://securetoken.google.com/${i}`,aud:i,iat:s,exp:s+3600,auth_time:s,sub:r,user_id:r,firebase:{sign_in_provider:"custom",identities:{}},...n};return[Nt(JSON.stringify(t)),Nt(JSON.stringify(o)),""].join(".")}/**
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
 */function sa(){return typeof navigator<"u"&&typeof navigator.userAgent=="string"?navigator.userAgent:""}function ts(){return typeof window<"u"&&!!(window.cordova||window.phonegap||window.PhoneGap)&&/ios|iphone|ipod|ipad|android|blackberry|iemobile/i.test(sa())}function ra(){return typeof window<"u"||ns()}function ns(){return typeof WorkerGlobalScope<"u"&&typeof self<"u"&&self instanceof WorkerGlobalScope}function oa(){return typeof navigator=="object"&&navigator.product==="ReactNative"}function aa(){return Yi.NODE_ADMIN===!0}function la(){try{return typeof indexedDB=="object"}catch{return!1}}function ca(){return new Promise((n,e)=>{try{let t=!0;const i="validate-browser-context-for-indexeddb-analytics-module",s=self.indexedDB.open(i);s.onsuccess=()=>{s.result.close(),t||self.indexedDB.deleteDatabase(i),n(!0)},s.onupgradeneeded=()=>{t=!1},s.onerror=()=>{var r;e(((r=s.error)==null?void 0:r.message)||"")}}catch(t){e(t)}})}/**
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
 */const ha="FirebaseError";class We extends Error{constructor(e,t,i){super(t),this.code=e,this.customData=i,this.name=ha,Object.setPrototypeOf(this,We.prototype),Error.captureStackTrace&&Error.captureStackTrace(this,is.prototype.create)}}class is{constructor(e,t,i){this.service=e,this.serviceName=t,this.errors=i}create(e,...t){const i=t[0]||{},s=`${this.service}/${e}`,r=this.errors[e],o=r?da(r,i):"Error",a=`${this.serviceName}: ${o} (${s}).`;return new We(s,a,i)}}function da(n,e){try{let t=0,i="";for(;t<n.length;){const s=n.indexOf("{$",t);if(s===-1){i+=n.substring(t);break}const r=n.indexOf("}",s+2);if(r===-1){i+=n.substring(t);break}const o=n.substring(s+2,r),a=e[o];i+=n.substring(t,s)+(a!=null?String(a):`<${o}?>`),t=r+1}return i}catch{return n}}/**
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
 */function tt(n){return JSON.parse(n)}function P(n){return JSON.stringify(n)}/**
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
 */const ss=function(n){let e={},t={},i={},s="";try{const r=n.split(".");e=tt(Rt(r[0])||""),t=tt(Rt(r[1])||""),s=r[2],i=t.d||{},delete t.d}catch{}return{header:e,claims:t,data:i,signature:s}},ua=function(n){const e=ss(n),t=e.claims;return!!t&&typeof t=="object"&&t.hasOwnProperty("iat")},fa=function(n){const e=ss(n).claims;return typeof e=="object"&&e.admin===!0};/**
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
 */function J(n,e){return Object.prototype.hasOwnProperty.call(n,e)}function Ie(n,e){if(Object.prototype.hasOwnProperty.call(n,e))return n[e]}function Cn(n){for(const e in n)if(Object.prototype.hasOwnProperty.call(n,e))return!1;return!0}function Pt(n,e,t){const i={};for(const s in n)Object.prototype.hasOwnProperty.call(n,s)&&(i[s]=e.call(t,n[s],s,n));return i}function Dt(n,e){if(n===e)return!0;const t=Object.keys(n),i=Object.keys(e);for(const s of t){if(!i.includes(s))return!1;const r=n[s],o=e[s];if(rs(r)&&rs(o)){if(!Dt(r,o))return!1}else if(r!==o)return!1}for(const s of i)if(!t.includes(s))return!1;return!0}function rs(n){return n!==null&&typeof n=="object"}/**
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
 */function pa(n){const e=[];for(const[t,i]of Object.entries(n))Array.isArray(i)?i.forEach(s=>{e.push(encodeURIComponent(t)+"="+encodeURIComponent(s))}):e.push(encodeURIComponent(t)+"="+encodeURIComponent(i));return e.length?"&"+e.join("&"):""}/**
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
 */class _a{constructor(){this.chain_=[],this.buf_=[],this.W_=[],this.pad_=[],this.inbuf_=0,this.total_=0,this.blockSize=512/8,this.pad_[0]=128;for(let e=1;e<this.blockSize;++e)this.pad_[e]=0;this.reset()}reset(){this.chain_[0]=1732584193,this.chain_[1]=4023233417,this.chain_[2]=2562383102,this.chain_[3]=271733878,this.chain_[4]=3285377520,this.inbuf_=0,this.total_=0}compress_(e,t){t||(t=0);const i=this.W_;if(typeof e=="string")for(let h=0;h<16;h++)i[h]=e.charCodeAt(t)<<24|e.charCodeAt(t+1)<<16|e.charCodeAt(t+2)<<8|e.charCodeAt(t+3),t+=4;else for(let h=0;h<16;h++)i[h]=e[t]<<24|e[t+1]<<16|e[t+2]<<8|e[t+3],t+=4;for(let h=16;h<80;h++){const u=i[h-3]^i[h-8]^i[h-14]^i[h-16];i[h]=(u<<1|u>>>31)&4294967295}let s=this.chain_[0],r=this.chain_[1],o=this.chain_[2],a=this.chain_[3],l=this.chain_[4],c,d;for(let h=0;h<80;h++){h<40?h<20?(c=a^r&(o^a),d=1518500249):(c=r^o^a,d=1859775393):h<60?(c=r&o|a&(r|o),d=2400959708):(c=r^o^a,d=3395469782);const u=(s<<5|s>>>27)+c+l+d+i[h]&4294967295;l=a,a=o,o=(r<<30|r>>>2)&4294967295,r=s,s=u}this.chain_[0]=this.chain_[0]+s&4294967295,this.chain_[1]=this.chain_[1]+r&4294967295,this.chain_[2]=this.chain_[2]+o&4294967295,this.chain_[3]=this.chain_[3]+a&4294967295,this.chain_[4]=this.chain_[4]+l&4294967295}update(e,t){if(e==null)return;t===void 0&&(t=e.length);const i=t-this.blockSize;let s=0;const r=this.buf_;let o=this.inbuf_;for(;s<t;){if(o===0)for(;s<=i;)this.compress_(e,s),s+=this.blockSize;if(typeof e=="string"){for(;s<t;)if(r[o]=e.charCodeAt(s),++o,++s,o===this.blockSize){this.compress_(r),o=0;break}}else for(;s<t;)if(r[o]=e[s],++o,++s,o===this.blockSize){this.compress_(r),o=0;break}}this.inbuf_=o,this.total_+=t}digest(){const e=[];let t=this.total_*8;this.inbuf_<56?this.update(this.pad_,56-this.inbuf_):this.update(this.pad_,this.blockSize-(this.inbuf_-56));for(let s=this.blockSize-1;s>=56;s--)this.buf_[s]=t&255,t/=256;this.compress_(this.buf_);let i=0;for(let s=0;s<5;s++)for(let r=24;r>=0;r-=8)e[i]=this.chain_[s]>>r&255,++i;return e}}function xe(n,e){return`${n} failed: ${e} argument `}/**
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
 */const ma=function(n){const e=[];let t=0;for(let i=0;i<n.length;i++){let s=n.charCodeAt(i);if(s>=55296&&s<=56319){const r=s-55296;i++,f(i<n.length,"Surrogate pair missing trail surrogate.");const o=n.charCodeAt(i)-56320;s=65536+(r<<10)+o}s<128?e[t++]=s:s<2048?(e[t++]=s>>6|192,e[t++]=s&63|128):s<65536?(e[t++]=s>>12|224,e[t++]=s>>6&63|128,e[t++]=s&63|128):(e[t++]=s>>18|240,e[t++]=s>>12&63|128,e[t++]=s>>6&63|128,e[t++]=s&63|128)}return e},Ot=function(n){let e=0;for(let t=0;t<n.length;t++){const i=n.charCodeAt(t);i<128?e++:i<2048?e+=2:i>=55296&&i<=56319?(e+=4,t++):e+=3}return e};/**
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
 */function os(n){try{return(n.startsWith("http://")||n.startsWith("https://")?new URL(n).hostname:n).endsWith(".cloudworkstations.dev")}catch{return!1}}async function ga(n){return(await fetch(n,{credentials:"include"})).ok}class Te{constructor(e,t,i){this.name=e,this.instanceFactory=t,this.type=i,this.multipleInstances=!1,this.serviceProps={},this.instantiationMode="LAZY",this.onInstanceCreated=null}setInstantiationMode(e){return this.instantiationMode=e,this}setMultipleInstances(e){return this.multipleInstances=e,this}setServiceProps(e){return this.serviceProps=e,this}setInstanceCreatedCallback(e){return this.onInstanceCreated=e,this}}/**
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
 */const Ae="[DEFAULT]";/**
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
 */class En{constructor(e,t){this.name=e,this.container=t,this.component=null,this.instances=new Map,this.instancesDeferred=new Map,this.instancesOptions=new Map,this.onInitCallbacks=new Map}get(e){const t=this.normalizeInstanceIdentifier(e);if(!this.instancesDeferred.has(t)){const i=new z;if(this.instancesDeferred.set(t,i),this.isInitialized(t)||this.shouldAutoInitialize())try{const s=this.getOrInitializeService({instanceIdentifier:t});s&&i.resolve(s)}catch{}}return this.instancesDeferred.get(t).promise}getImmediate(e){const t=this.normalizeInstanceIdentifier(e==null?void 0:e.identifier),i=(e==null?void 0:e.optional)??!1;if(this.isInitialized(t)||this.shouldAutoInitialize())try{return this.getOrInitializeService({instanceIdentifier:t})}catch(s){if(i)return null;throw s}else{if(i)return null;throw Error(`Service ${this.name} is not available`)}}getComponent(){return this.component}setComponent(e){if(e.name!==this.name)throw Error(`Mismatching Component ${e.name} for Provider ${this.name}.`);if(this.component)throw Error(`Component for ${this.name} has already been provided`);if(this.component=e,!!this.shouldAutoInitialize()){if(va(e))try{this.getOrInitializeService({instanceIdentifier:Ae})}catch{}for(const[t,i]of this.instancesDeferred.entries()){const s=this.normalizeInstanceIdentifier(t);try{const r=this.getOrInitializeService({instanceIdentifier:s});i.resolve(r)}catch{}}}}clearInstance(e=Ae){this.instancesDeferred.delete(e),this.instancesOptions.delete(e),this.instances.delete(e)}async delete(){const e=Array.from(this.instances.values());await Promise.all([...e.filter(t=>"INTERNAL"in t).map(t=>t.INTERNAL.delete()),...e.filter(t=>"_delete"in t).map(t=>t._delete())])}isComponentSet(){return this.component!=null}isInitialized(e=Ae){return this.instances.has(e)}getOptions(e=Ae){return this.instancesOptions.get(e)||{}}initialize(e={}){const{options:t={}}=e,i=this.normalizeInstanceIdentifier(e.instanceIdentifier);if(this.isInitialized(i))throw Error(`${this.name}(${i}) has already been initialized`);if(!this.isComponentSet())throw Error(`Component ${this.name} has not been registered yet`);const s=this.getOrInitializeService({instanceIdentifier:i,options:t});for(const[r,o]of this.instancesDeferred.entries()){const a=this.normalizeInstanceIdentifier(r);i===a&&o.resolve(s)}return s}onInit(e,t){const i=this.normalizeInstanceIdentifier(t),s=this.onInitCallbacks.get(i)??new Set;s.add(e),this.onInitCallbacks.set(i,s);const r=this.instances.get(i);return r&&e(r,i),()=>{s.delete(e)}}invokeOnInitCallbacks(e,t){const i=this.onInitCallbacks.get(t);if(i)for(const s of i)try{s(e,t)}catch{}}getOrInitializeService({instanceIdentifier:e,options:t={}}){let i=this.instances.get(e);if(!i&&this.component&&(i=this.component.instanceFactory(this.container,{instanceIdentifier:ya(e),options:t}),this.instances.set(e,i),this.instancesOptions.set(e,t),this.invokeOnInitCallbacks(i,e),this.component.onInstanceCreated))try{this.component.onInstanceCreated(this.container,e,i)}catch{}return i||null}normalizeInstanceIdentifier(e=Ae){return this.component?this.component.multipleInstances?e:Ae:e}shouldAutoInitialize(){return!!this.component&&this.component.instantiationMode!=="EXPLICIT"}}function ya(n){return n===Ae?void 0:n}function va(n){return n.instantiationMode==="EAGER"}/**
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
 */class Sn{constructor(e){this.name=e,this.providers=new Map}addComponent(e){const t=this.getProvider(e.name);if(t.isComponentSet())throw new Error(`Component ${e.name} has already been registered with ${this.name}`);t.setComponent(e)}addOrOverwriteComponent(e){this.getProvider(e.name).isComponentSet()&&this.providers.delete(e.name),this.addComponent(e)}getProvider(e){if(this.providers.has(e))return this.providers.get(e);const t=new En(e,this);return this.providers.set(e,t),t}getProviders(){return Array.from(this.providers.values())}}/**
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
 */const In=[];var I;(function(n){n[n.DEBUG=0]="DEBUG",n[n.VERBOSE=1]="VERBOSE",n[n.INFO=2]="INFO",n[n.WARN=3]="WARN",n[n.ERROR=4]="ERROR",n[n.SILENT=5]="SILENT"})(I||(I={}));const as={debug:I.DEBUG,verbose:I.VERBOSE,info:I.INFO,warn:I.WARN,error:I.ERROR,silent:I.SILENT},ba=I.INFO,wa={[I.DEBUG]:"log",[I.VERBOSE]:"log",[I.INFO]:"info",[I.WARN]:"warn",[I.ERROR]:"error"},Ca=(n,e,...t)=>{if(e<n.logLevel)return;const i=new Date().toISOString(),s=wa[e];if(s)console[s](`[${i}]  ${n.name}:`,...t);else throw new Error(`Attempted to log a message with an invalid logType (value: ${e})`)};class ls{constructor(e){this.name=e,this._logLevel=ba,this._logHandler=Ca,this._userLogHandler=null,In.push(this)}get logLevel(){return this._logLevel}set logLevel(e){if(!(e in I))throw new TypeError(`Invalid value "${e}" assigned to \`logLevel\``);this._logLevel=e}setLogLevel(e){this._logLevel=typeof e=="string"?as[e]:e}get logHandler(){return this._logHandler}set logHandler(e){if(typeof e!="function")throw new TypeError("Value assigned to `logHandler` must be a function");this._logHandler=e}get userLogHandler(){return this._userLogHandler}set userLogHandler(e){this._userLogHandler=e}debug(...e){this._userLogHandler&&this._userLogHandler(this,I.DEBUG,...e),this._logHandler(this,I.DEBUG,...e)}log(...e){this._userLogHandler&&this._userLogHandler(this,I.VERBOSE,...e),this._logHandler(this,I.VERBOSE,...e)}info(...e){this._userLogHandler&&this._userLogHandler(this,I.INFO,...e),this._logHandler(this,I.INFO,...e)}warn(...e){this._userLogHandler&&this._userLogHandler(this,I.WARN,...e),this._logHandler(this,I.WARN,...e)}error(...e){this._userLogHandler&&this._userLogHandler(this,I.ERROR,...e),this._logHandler(this,I.ERROR,...e)}}function Ea(n){In.forEach(e=>{e.setLogLevel(n)})}function Sa(n,e){for(const t of In){let i=null;e&&e.level&&(i=as[e.level]),n===null?t.userLogHandler=null:t.userLogHandler=(s,r,...o)=>{const a=o.map(l=>{if(l==null)return null;if(typeof l=="string")return l;if(typeof l=="number"||typeof l=="boolean")return l.toString();if(l instanceof Error)return l.message;try{return JSON.stringify(l)}catch{return null}}).filter(l=>l).join(" ");r>=(i??s.logLevel)&&n({level:I[r].toLowerCase(),message:a,args:o,type:s.name})}}}const Ia=(n,e)=>e.some(t=>n instanceof t);let cs,hs;function xa(){return cs||(cs=[IDBDatabase,IDBObjectStore,IDBIndex,IDBCursor,IDBTransaction])}function Ta(){return hs||(hs=[IDBCursor.prototype.advance,IDBCursor.prototype.continue,IDBCursor.prototype.continuePrimaryKey])}const ds=new WeakMap,xn=new WeakMap,us=new WeakMap,Tn=new WeakMap,An=new WeakMap;function Aa(n){const e=new Promise((t,i)=>{const s=()=>{n.removeEventListener("success",r),n.removeEventListener("error",o)},r=()=>{t(de(n.result)),s()},o=()=>{i(n.error),s()};n.addEventListener("success",r),n.addEventListener("error",o)});return e.then(t=>{t instanceof IDBCursor&&ds.set(t,n)}).catch(()=>{}),An.set(e,n),e}function ka(n){if(xn.has(n))return;const e=new Promise((t,i)=>{const s=()=>{n.removeEventListener("complete",r),n.removeEventListener("error",o),n.removeEventListener("abort",o)},r=()=>{t(),s()},o=()=>{i(n.error||new DOMException("AbortError","AbortError")),s()};n.addEventListener("complete",r),n.addEventListener("error",o),n.addEventListener("abort",o)});xn.set(n,e)}let kn={get(n,e,t){if(n instanceof IDBTransaction){if(e==="done")return xn.get(n);if(e==="objectStoreNames")return n.objectStoreNames||us.get(n);if(e==="store")return t.objectStoreNames[1]?void 0:t.objectStore(t.objectStoreNames[0])}return de(n[e])},set(n,e,t){return n[e]=t,!0},has(n,e){return n instanceof IDBTransaction&&(e==="done"||e==="store")?!0:e in n}};function Na(n){kn=n(kn)}function Ra(n){return n===IDBDatabase.prototype.transaction&&!("objectStoreNames"in IDBTransaction.prototype)?function(e,...t){const i=n.call(Nn(this),e,...t);return us.set(i,e.sort?e.sort():[e]),de(i)}:Ta().includes(n)?function(...e){return n.apply(Nn(this),e),de(ds.get(this))}:function(...e){return de(n.apply(Nn(this),e))}}function Pa(n){return typeof n=="function"?Ra(n):(n instanceof IDBTransaction&&ka(n),Ia(n,xa())?new Proxy(n,kn):n)}function de(n){if(n instanceof IDBRequest)return Aa(n);if(Tn.has(n))return Tn.get(n);const e=Pa(n);return e!==n&&(Tn.set(n,e),An.set(e,n)),e}const Nn=n=>An.get(n);function Da(n,e,{blocked:t,upgrade:i,blocking:s,terminated:r}={}){const o=indexedDB.open(n,e),a=de(o);return i&&o.addEventListener("upgradeneeded",l=>{i(de(o.result),l.oldVersion,l.newVersion,de(o.transaction),l)}),t&&o.addEventListener("blocked",l=>t(l.oldVersion,l.newVersion,l)),a.then(l=>{r&&l.addEventListener("close",()=>r()),s&&l.addEventListener("versionchange",c=>s(c.oldVersion,c.newVersion,c))}).catch(()=>{}),a}const Oa=["get","getKey","getAll","getAllKeys","count"],Ma=["put","add","delete","clear"],Rn=new Map;function fs(n,e){if(!(n instanceof IDBDatabase&&!(e in n)&&typeof e=="string"))return;if(Rn.get(e))return Rn.get(e);const t=e.replace(/FromIndex$/,""),i=e!==t,s=Ma.includes(t);if(!(t in(i?IDBIndex:IDBObjectStore).prototype)||!(s||Oa.includes(t)))return;const r=async function(o,...a){const l=this.transaction(o,s?"readwrite":"readonly");let c=l.store;return i&&(c=c.index(a.shift())),(await Promise.all([c[t](...a),s&&l.done]))[0]};return Rn.set(e,r),r}Na(n=>({...n,get:(e,t,i)=>fs(e,t)||n.get(e,t,i),has:(e,t)=>!!fs(e,t)||n.has(e,t)}));/**
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
 */class La{constructor(e){this.container=e}getPlatformInfoString(){return this.container.getProviders().map(t=>{if(Fa(t)){const i=t.getImmediate();return`${i.library}/${i.version}`}else return null}).filter(t=>t).join(" ")}}function Fa(n){const e=n.getComponent();return(e==null?void 0:e.type)==="VERSION"}const Mt="@firebase/app",Pn="0.16.2";/**
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
 */const le=new ls("@firebase/app"),Ba="@firebase/app-compat",Wa="@firebase/analytics-compat",Ua="@firebase/analytics",$a="@firebase/app-check-compat",Ha="@firebase/app-check",Va="@firebase/auth",za="@firebase/auth-compat",ja="@firebase/database",Ga="@firebase/data-connect",qa="@firebase/database-compat",Ka="@firebase/functions",Qa="@firebase/functions-compat",Ya="@firebase/installations",Ja="@firebase/installations-compat",Xa="@firebase/messaging",Za="@firebase/messaging-compat",el="@firebase/performance",tl="@firebase/performance-compat",nl="@firebase/remote-config",il="@firebase/remote-config-compat",sl="@firebase/storage",rl="@firebase/storage-compat",ol="@firebase/firestore",al="@firebase/ai",ll="@firebase/firestore-compat",cl="firebase",hl="12.19.0";/**
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
 */const nt="[DEFAULT]",dl={[Mt]:"fire-core",[Ba]:"fire-core-compat",[Ua]:"fire-analytics",[Wa]:"fire-analytics-compat",[Ha]:"fire-app-check",[$a]:"fire-app-check-compat",[Va]:"fire-auth",[za]:"fire-auth-compat",[ja]:"fire-rtdb",[Ga]:"fire-data-connect",[qa]:"fire-rtdb-compat",[Ka]:"fire-fn",[Qa]:"fire-fn-compat",[Ya]:"fire-iid",[Ja]:"fire-iid-compat",[Xa]:"fire-fcm",[Za]:"fire-fcm-compat",[el]:"fire-perf",[tl]:"fire-perf-compat",[nl]:"fire-rc",[il]:"fire-rc-compat",[sl]:"fire-gcs",[rl]:"fire-gcs-compat",[ol]:"fire-fst",[ll]:"fire-fst-compat",[al]:"fire-vertex","fire-js":"fire-js",[cl]:"fire-js-all"};/**
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
 */const ue=new Map,Ue=new Map,$e=new Map;function Dn(n,e){try{n.container.addComponent(e)}catch(t){le.debug(`Component ${e.name} failed to register with FirebaseApp ${n.name}`,t)}}function ul(n,e){n.container.addOrOverwriteComponent(e)}function it(n){const e=n.name;if($e.has(e))return le.debug(`There were multiple attempts to register component ${e}.`),!1;$e.set(e,n);for(const t of ue.values())Dn(t,n);for(const t of Ue.values())Dn(t,n);return!0}function On(n,e){const t=n.container.getProvider("heartbeat").getImmediate({optional:!0});return t&&t.triggerHeartbeat(),n.container.getProvider(e)}function fl(n,e,t=nt){On(n,e).clearInstance(t)}function Mn(n){return n.options!==void 0}function ps(n){return Mn(n)?!1:"authIdToken"in n||"appCheckToken"in n||"releaseOnDeref"in n||"automaticDataCollectionEnabled"in n}function _s(n){return n==null?!1:n.settings!==void 0}function pl(){$e.clear()}/**
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
 */const _l={"no-app":"No Firebase App '{$appName}' has been created - call initializeApp() first","bad-app-name":"Illegal App name: '{$appName}'","duplicate-app":"Firebase App named '{$appName}' already exists with different {$mismatchedParam}. Existing: '{$oldValue}'. New: '{$newValue}'.","app-deleted":"Firebase App named '{$appName}' already deleted","server-app-deleted":"Firebase Server App has been deleted","no-options":"Need to provide options, when not being deployed to hosting via source.","invalid-app-argument":"firebase.{$appName}() takes either no argument or a Firebase App instance.","invalid-log-argument":"First argument to `onLog` must be null or a function.","idb-open":"Error thrown when opening IndexedDB. Original error: {$originalErrorMessage}.","idb-get":"Error thrown when reading from IndexedDB. Original error: {$originalErrorMessage}.","idb-set":"Error thrown when writing to IndexedDB. Original error: {$originalErrorMessage}.","idb-delete":"Error thrown when deleting from IndexedDB. Original error: {$originalErrorMessage}.","finalization-registry-not-supported":"FirebaseServerApp deleteOnDeref field defined but the JS runtime does not support FinalizationRegistry.","invalid-server-app-environment":"FirebaseServerApp is not for use in browser environments."},$=new is("app","Firebase",_l);/**
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
 */class ms{constructor(e,t,i){this._isDeleted=!1,this._options={...e},this._config={...t},this._name=t.name,this._automaticDataCollectionEnabled=t.automaticDataCollectionEnabled,this._container=i,this.container.addComponent(new Te("app",()=>this,"PUBLIC"))}get automaticDataCollectionEnabled(){return this.checkDestroyed(),this._automaticDataCollectionEnabled}set automaticDataCollectionEnabled(e){this.checkDestroyed(),this._automaticDataCollectionEnabled=e}get name(){return this.checkDestroyed(),this._name}get options(){return this.checkDestroyed(),this._options}get config(){return this.checkDestroyed(),this._config}get container(){return this._container}get isDeleted(){return this._isDeleted}set isDeleted(e){this._isDeleted=e}checkDestroyed(){if(this.isDeleted)throw $.create("app-deleted",{appName:this._name})}}/**
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
 */function gs(n,e){const t=Rt(n.split(".")[1]);if(t===null){console.error(`FirebaseServerApp ${e} is invalid: second part could not be parsed.`);return}if(JSON.parse(t).exp===void 0){console.error(`FirebaseServerApp ${e} is invalid: expiration claim could not be parsed`);return}const s=JSON.parse(t).exp*1e3,r=new Date().getTime();s-r<=0&&console.error(`FirebaseServerApp ${e} is invalid: the token has expired.`)}class ml extends ms{constructor(e,t,i,s){const r=t.automaticDataCollectionEnabled!==void 0?t.automaticDataCollectionEnabled:!0,o={name:i,automaticDataCollectionEnabled:r};if(e.apiKey!==void 0)super(e,o,s);else{const a=e;super(a.options,o,s)}this._serverConfig={automaticDataCollectionEnabled:r,...t},this._serverConfig.authIdToken&&gs(this._serverConfig.authIdToken,"authIdToken"),this._serverConfig.appCheckToken&&gs(this._serverConfig.appCheckToken,"appCheckToken"),this._finalizationRegistry=null,typeof FinalizationRegistry<"u"&&(this._finalizationRegistry=new FinalizationRegistry(()=>{this.automaticCleanup()})),this._refCount=0,this.incRefCount(this._serverConfig.releaseOnDeref),this._serverConfig.releaseOnDeref=void 0,t.releaseOnDeref=void 0,fe(Mt,Pn,"serverapp")}toJSON(){}get refCount(){return this._refCount}incRefCount(e){this.isDeleted||(this._refCount++,e!==void 0&&this._finalizationRegistry!==null&&this._finalizationRegistry.register(e,this))}decRefCount(){return this.isDeleted?0:--this._refCount}automaticCleanup(){ws(this)}get settings(){return this.checkDestroyed(),this._serverConfig}checkDestroyed(){if(this.isDeleted)throw $.create("server-app-deleted")}}/**
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
 */const ys=hl;function vs(n,e={}){let t=n;typeof e!="object"&&(e={name:e});const i={name:nt,automaticDataCollectionEnabled:!0,...e},s=i.name;if(typeof s!="string"||!s)throw $.create("bad-app-name",{appName:String(s)});if(t||(t=wn()),!t)throw $.create("no-options");const r=ue.get(s);if(r)if(Dt(t,r.options)){if(Dt(i,r.config))return r;throw $.create("duplicate-app",{appName:s,mismatchedParam:"config",oldValue:JSON.stringify(r.config),newValue:JSON.stringify(i)})}else throw $.create("duplicate-app",{appName:s,mismatchedParam:"options",oldValue:JSON.stringify(r.options),newValue:JSON.stringify(t)});const o=new Sn(s);for(const l of $e.values())o.addComponent(l);const a=new ms(t,i,o);return ue.set(s,a),a}function gl(n,e={}){if(ra()&&!ns())throw $.create("invalid-server-app-environment");let t,i=e||{};if(n&&(Mn(n)?t=n.options:ps(n)?i=n:t=n),i.automaticDataCollectionEnabled===void 0&&(i.automaticDataCollectionEnabled=!0),t||(t=wn()),!t)throw $.create("no-options");const s={...i,...t};s.releaseOnDeref!==void 0&&delete s.releaseOnDeref;const r=d=>[...d].reduce((h,u)=>Math.imul(31,h)+u.charCodeAt(0)|0,0);if(i.releaseOnDeref!==void 0&&typeof FinalizationRegistry>"u")throw $.create("finalization-registry-not-supported",{});const o=""+r(JSON.stringify(s)),a=Ue.get(o);if(a)return a.incRefCount(i.releaseOnDeref),a;const l=new Sn(o);for(const d of $e.values())l.addComponent(d);const c=new ml(t,i,o,l);return Ue.set(o,c),c}function bs(n=nt){const e=ue.get(n);if(!e&&n===nt&&wn())return vs();if(!e)throw $.create("no-app",{appName:n});return e}function yl(){return Array.from(ue.values())}async function ws(n){let e=!1;const t=n.name;ue.has(t)?(e=!0,ue.delete(t)):Ue.has(t)&&n.decRefCount()<=0&&(Ue.delete(t),e=!0),e&&(await Promise.all(n.container.getProviders().map(i=>i.delete())),n.isDeleted=!0)}function fe(n,e,t){let i=dl[n]??n;t&&(i+=`-${t}`);const s=i.match(/\s|\//),r=e.match(/\s|\//);if(s||r){const o=[`Unable to register library "${i}" with version "${e}":`];s&&o.push(`library name "${i}" contains illegal characters (whitespace or "/")`),s&&r&&o.push("and"),r&&o.push(`version name "${e}" contains illegal characters (whitespace or "/")`),le.warn(o.join(" "));return}it(new Te(`${i}-version`,()=>({library:i,version:e}),"VERSION"))}function vl(n,e){if(n!==null&&typeof n!="function")throw $.create("invalid-log-argument");Sa(n,e)}function bl(n){Ea(n)}/**
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
 */const wl="firebase-heartbeat-database",Cl=1,st="firebase-heartbeat-store";let Ln=null;function Cs(){return Ln||(Ln=Da(wl,Cl,{upgrade:(n,e)=>{switch(e){case 0:try{n.createObjectStore(st)}catch(t){console.warn(t)}}}}).catch(n=>{throw $.create("idb-open",{originalErrorMessage:n.message})})),Ln}async function El(n){try{const t=(await Cs()).transaction(st),i=await t.objectStore(st).get(Ss(n));return await t.done,i}catch(e){if(e instanceof We)le.warn(e.message);else{const t=$.create("idb-get",{originalErrorMessage:e==null?void 0:e.message});le.warn(t.message)}}}async function Es(n,e){try{const i=(await Cs()).transaction(st,"readwrite");await i.objectStore(st).put(e,Ss(n)),await i.done}catch(t){if(t instanceof We)le.warn(t.message);else{const i=$.create("idb-set",{originalErrorMessage:t==null?void 0:t.message});le.warn(i.message)}}}function Ss(n){return`${n.name}!${n.options.appId}`}/**
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
 */const Sl=1024,Il=30;class xl{constructor(e){this.container=e,this._heartbeatsCache=null;const t=this.container.getProvider("app").getImmediate();this._storage=new Al(t),this._heartbeatsCachePromise=this._storage.read().then(i=>(this._heartbeatsCache=i,i))}async triggerHeartbeat(){var e,t;try{const s=this.container.getProvider("platform-logger").getImmediate().getPlatformInfoString(),r=Is();if(((e=this._heartbeatsCache)==null?void 0:e.heartbeats)==null&&(this._heartbeatsCache=await this._heartbeatsCachePromise,((t=this._heartbeatsCache)==null?void 0:t.heartbeats)==null)||this._heartbeatsCache.lastSentHeartbeatDate===r||this._heartbeatsCache.heartbeats.some(o=>o.date===r))return;if(this._heartbeatsCache.heartbeats.push({date:r,agent:s}),this._heartbeatsCache.heartbeats.length>Il){const o=kl(this._heartbeatsCache.heartbeats);this._heartbeatsCache.heartbeats.splice(o,1)}return this._storage.overwrite(this._heartbeatsCache)}catch(i){le.warn(i)}}async getHeartbeatsHeader(){var e;try{if(this._heartbeatsCache===null&&await this._heartbeatsCachePromise,((e=this._heartbeatsCache)==null?void 0:e.heartbeats)==null||this._heartbeatsCache.heartbeats.length===0)return"";const t=Is(),{heartbeatsToSend:i,unsentEntries:s}=Tl(this._heartbeatsCache.heartbeats),r=Nt(JSON.stringify({version:2,heartbeats:i}));return this._heartbeatsCache.lastSentHeartbeatDate=t,s.length>0?(this._heartbeatsCache.heartbeats=s,await this._storage.overwrite(this._heartbeatsCache)):(this._heartbeatsCache.heartbeats=[],this._storage.overwrite(this._heartbeatsCache)),r}catch(t){return le.warn(t),""}}}function Is(){return new Date().toISOString().substring(0,10)}function Tl(n,e=Sl){const t=[];let i=n.slice();for(const s of n){const r=t.find(o=>o.agent===s.agent);if(r){if(r.dates.push(s.date),xs(t)>e){r.dates.pop();break}}else if(t.push({agent:s.agent,dates:[s.date]}),xs(t)>e){t.pop();break}i=i.slice(1)}return{heartbeatsToSend:t,unsentEntries:i}}class Al{constructor(e){this.app=e,this._canUseIndexedDBPromise=this.runIndexedDBEnvironmentCheck()}async runIndexedDBEnvironmentCheck(){return la()?ca().then(()=>!0).catch(()=>!1):!1}async read(){if(await this._canUseIndexedDBPromise){const t=await El(this.app);return t!=null&&t.heartbeats?t:{heartbeats:[]}}else return{heartbeats:[]}}async overwrite(e){if(await this._canUseIndexedDBPromise){const i=await this.read();return Es(this.app,{lastSentHeartbeatDate:e.lastSentHeartbeatDate??i.lastSentHeartbeatDate,heartbeats:e.heartbeats})}else return}async add(e){if(await this._canUseIndexedDBPromise){const i=await this.read();return Es(this.app,{lastSentHeartbeatDate:e.lastSentHeartbeatDate??i.lastSentHeartbeatDate,heartbeats:[...i.heartbeats,...e.heartbeats]})}else return}}function xs(n){return Nt(JSON.stringify({version:2,heartbeats:n})).length}function kl(n){if(n.length===0)return-1;let e=0,t=n[0].date;for(let i=1;i<n.length;i++)n[i].date<t&&(t=n[i].date,e=i);return e}/**
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
 */function Nl(n){it(new Te("platform-logger",e=>new La(e),"PRIVATE")),it(new Te("heartbeat",e=>new xl(e),"PRIVATE")),fe(Mt,Pn,n),fe(Mt,Pn,"esm2020"),fe("fire-js","")}/**
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
 */Nl("");var Rl="firebase",Pl="12.19.0";/**
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
 */fe(Rl,Pl,"app");const Dl=Object.freeze(Object.defineProperty({__proto__:null,FirebaseError:We,SDK_VERSION:ys,_DEFAULT_ENTRY_NAME:nt,_addComponent:Dn,_addOrOverwriteComponent:ul,_apps:ue,_clearComponents:pl,_components:$e,_getProvider:On,_isFirebaseApp:Mn,_isFirebaseServerApp:_s,_isFirebaseServerAppSettings:ps,_registerComponent:it,_removeServiceInstance:fl,_serverApps:Ue,deleteApp:ws,getApp:bs,getApps:yl,initializeApp:vs,initializeServerApp:gl,onLog:vl,registerVersion:fe,setLogLevel:bl},Symbol.toStringTag,{value:"Module"})),Ts="@firebase/database",As="1.1.5";/**
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
 */let ks="";function Fn(n){ks=n}/**
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
 */class Ol{constructor(e){this.domStorage_=e,this.prefix_="firebase:"}set(e,t){t==null?this.domStorage_.removeItem(this.prefixedName_(e)):this.domStorage_.setItem(this.prefixedName_(e),P(t))}get(e){const t=this.domStorage_.getItem(this.prefixedName_(e));return t==null?null:tt(t)}remove(e){this.domStorage_.removeItem(this.prefixedName_(e))}prefixedName_(e){return this.prefix_+e}toString(){return this.domStorage_.toString()}}/**
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
 */class Ml{constructor(){this.cache_={},this.isInMemoryStorage=!0}set(e,t){t==null?delete this.cache_[e]:this.cache_[e]=t}get(e){return J(this.cache_,e)?this.cache_[e]:null}remove(e){delete this.cache_[e]}}/**
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
 */const Ns=function(n){try{if(typeof window<"u"&&typeof window[n]<"u"){const e=window[n];return e.setItem("firebase:sentinel","cache"),e.removeItem("firebase:sentinel"),new Ol(e)}}catch{}return new Ml},ke=Ns("localStorage"),Bn=Ns("sessionStorage");/**
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
 */const He=new ls("@firebase/database"),Rs=(function(){let n=1;return function(){return n++}})(),Ps=function(n){const e=ma(n),t=new _a;t.update(e);const i=t.digest();return bn.encodeByteArray(i)},rt=function(...n){let e="";for(let t=0;t<n.length;t++){const i=n[t];Array.isArray(i)||i&&typeof i=="object"&&typeof i.length=="number"?e+=rt.apply(null,i):typeof i=="object"?e+=P(i):e+=i,e+=" "}return e};let Ne=null,Ds=!0;const Os=function(n,e){f(!e||n===!0||n===!1,"Can't turn on custom loggers persistently."),n===!0?(He.logLevel=I.VERBOSE,Ne=He.log.bind(He),e&&Bn.set("logging_enabled",!0)):typeof n=="function"?Ne=n:(Ne=null,Bn.remove("logging_enabled"))},D=function(...n){if(Ds===!0&&(Ds=!1,Ne===null&&Bn.get("logging_enabled")===!0&&Os(!0)),Ne){const e=rt.apply(null,n);Ne(e)}},ot=function(n){return function(...e){D(n,...e)}},Wn=function(...n){const e="FIREBASE INTERNAL ERROR: "+rt(...n);He.error(e)},te=function(...n){const e=`FIREBASE FATAL ERROR: ${rt(...n)}`;throw He.error(e),new Error(e)},B=function(...n){const e="FIREBASE WARNING: "+rt(...n);He.warn(e)},Ll=function(){typeof window<"u"&&window.location&&window.location.protocol&&window.location.protocol.indexOf("https:")!==-1&&B("Insecure Firebase access from a secure page. Please use https in calls to new Firebase().")},Lt=function(n){return typeof n=="number"&&(n!==n||n===Number.POSITIVE_INFINITY||n===Number.NEGATIVE_INFINITY)},Fl=function(n){if(document.readyState==="complete")n();else{let e=!1;const t=function(){if(!document.body){setTimeout(t,Math.floor(10));return}e||(e=!0,n())};document.addEventListener?(document.addEventListener("DOMContentLoaded",t,!1),window.addEventListener("load",t,!1)):document.attachEvent&&(document.attachEvent("onreadystatechange",()=>{document.readyState==="complete"&&t()}),window.attachEvent("onload",t))}},pe="[MIN_NAME]",ce="[MAX_NAME]",Re=function(n,e){if(n===e)return 0;if(n===pe||e===ce)return-1;if(e===pe||n===ce)return 1;{const t=Fs(n),i=Fs(e);return t!==null?i!==null?t-i===0?n.length-e.length:t-i:-1:i!==null?1:n<e?-1:1}},Bl=function(n,e){return n===e?0:n<e?-1:1},at=function(n,e){if(e&&n in e)return e[n];throw new Error("Missing required key ("+n+") in object: "+P(e))},Un=function(n){if(typeof n!="object"||n===null)return P(n);const e=[];for(const i in n)e.push(i);e.sort();let t="{";for(let i=0;i<e.length;i++)i!==0&&(t+=","),t+=P(e[i]),t+=":",t+=Un(n[e[i]]);return t+="}",t},Ms=function(n,e){const t=n.length;if(t<=e)return[n];const i=[];for(let s=0;s<t;s+=e)s+e>t?i.push(n.substring(s,t)):i.push(n.substring(s,s+e));return i};function O(n,e){for(const t in n)n.hasOwnProperty(t)&&e(t,n[t])}const Ls=function(n){f(!Lt(n),"Invalid JSON number");const e=11,t=52,i=(1<<e-1)-1;let s,r,o,a,l;n===0?(r=0,o=0,s=1/n===-1/0?1:0):(s=n<0,n=Math.abs(n),n>=Math.pow(2,1-i)?(a=Math.min(Math.floor(Math.log(n)/Math.LN2),i),r=a+i,o=Math.round(n*Math.pow(2,t-a)-Math.pow(2,t))):(r=0,o=Math.round(n/Math.pow(2,1-i-t))));const c=[];for(l=t;l;l-=1)c.push(o%2?1:0),o=Math.floor(o/2);for(l=e;l;l-=1)c.push(r%2?1:0),r=Math.floor(r/2);c.push(s?1:0),c.reverse();const d=c.join("");let h="";for(l=0;l<64;l+=8){let u=parseInt(d.substr(l,8),2).toString(16);u.length===1&&(u="0"+u),h=h+u}return h.toLowerCase()},Wl=function(){return!!(typeof window=="object"&&window.chrome&&window.chrome.extension&&!/^chrome/.test(window.location.href))},Ul=function(){return typeof Windows=="object"&&typeof Windows.UI=="object"};function $l(n,e){let t="Unknown Error";n==="too_big"?t="The data requested exceeds the maximum size that can be accessed with a single request.":n==="permission_denied"?t="Client doesn't have permission to access the desired data.":n==="unavailable"&&(t="The service is unavailable");const i=new Error(n+" at "+e._path.toString()+": "+t);return i.code=n.toUpperCase(),i}const Hl=new RegExp("^-?(0*)\\d{1,10}$"),Vl=-2147483648,zl=2147483647,Fs=function(n){if(Hl.test(n)){const e=Number(n);if(e>=Vl&&e<=zl)return e}return null},Ve=function(n){try{n()}catch(e){setTimeout(()=>{const t=e.stack||"";throw B("Exception was thrown by user callback.",t),e},Math.floor(0))}},jl=function(){return(typeof window=="object"&&window.navigator&&window.navigator.userAgent||"").search(/googlebot|google webmaster tools|bingbot|yahoo! slurp|baiduspider|yandexbot|duckduckbot/i)>=0},lt=function(n,e){const t=setTimeout(n,e);return typeof t=="number"&&typeof Deno<"u"&&Deno.unrefTimer?Deno.unrefTimer(t):typeof t=="object"&&t.unref&&t.unref(),t};/**
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
 */class Gl{constructor(e,t){this.appCheckProvider=t,this.appName=e.name,_s(e)&&e.settings.appCheckToken&&(this.serverAppAppCheckToken=e.settings.appCheckToken),this.appCheck=t==null?void 0:t.getImmediate({optional:!0}),this.appCheck||t==null||t.get().then(i=>this.appCheck=i)}getToken(e){if(this.serverAppAppCheckToken){if(e)throw new Error("Attempted reuse of `FirebaseServerApp.appCheckToken` after previous usage failed.");return Promise.resolve({token:this.serverAppAppCheckToken})}return this.appCheck?this.appCheck.getToken(e):new Promise((t,i)=>{setTimeout(()=>{this.appCheck?this.getToken(e).then(t,i):t(null)},0)})}addTokenChangeListener(e){var t;(t=this.appCheckProvider)==null||t.get().then(i=>i.addTokenListener(e))}notifyForInvalidToken(){B(`Provided AppCheck credentials for the app named "${this.appName}" are invalid. This usually indicates your app was not initialized correctly.`)}}/**
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
 */class ql{constructor(e,t,i){this.appName_=e,this.firebaseOptions_=t,this.authProvider_=i,this.auth_=null,this.auth_=i.getImmediate({optional:!0}),this.auth_||i.onInit(s=>this.auth_=s)}getToken(e){return this.auth_?this.auth_.getToken(e).catch(t=>t&&t.code==="auth/token-not-initialized"?(D("Got auth/token-not-initialized error.  Treating as null token."),null):Promise.reject(t)):new Promise((t,i)=>{setTimeout(()=>{this.auth_?this.getToken(e).then(t,i):t(null)},0)})}addTokenChangeListener(e){this.auth_?this.auth_.addAuthTokenListener(e):this.authProvider_.get().then(t=>t.addAuthTokenListener(e))}removeTokenChangeListener(e){this.authProvider_.get().then(t=>t.removeAuthTokenListener(e))}notifyForInvalidToken(){let e='Provided authentication credentials for the app named "'+this.appName_+'" are invalid. This usually indicates your app was not initialized correctly. ';"credential"in this.firebaseOptions_?e+='Make sure the "credential" property provided to initializeApp() is authorized to access the specified "databaseURL" and is from the correct project.':"serviceAccount"in this.firebaseOptions_?e+='Make sure the "serviceAccount" property provided to initializeApp() is authorized to access the specified "databaseURL" and is from the correct project.':e+='Make sure the "apiKey" and "databaseURL" properties provided to initializeApp() match the values provided for your app at https://console.firebase.google.com/.',B(e)}}class ze{constructor(e){this.accessToken=e}getToken(e){return Promise.resolve({accessToken:this.accessToken})}addTokenChangeListener(e){e(this.accessToken)}removeTokenChangeListener(e){}notifyForInvalidToken(){}}ze.OWNER="owner";/**
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
 */const $n="5",Bs="v",Ws="s",Us="r",$s="f",Hs=/(console\.firebase|firebase-console-\w+\.corp|firebase\.corp)\.google\.com/,Vs="ls",zs="p",Hn="ac",js="websocket",Gs="long_polling";/**
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
 */class qs{constructor(e,t,i,s,r=!1,o="",a=!1,l=!1,c=null){this.secure=t,this.namespace=i,this.webSocketOnly=s,this.nodeAdmin=r,this.persistenceKey=o,this.includeNamespaceInQueryParams=a,this.isUsingEmulator=l,this.emulatorOptions=c,this._host=e.toLowerCase(),this._domain=this._host.substr(this._host.indexOf(".")+1),this.internalHost=ke.get("host:"+e)||this._host}isCacheableHost(){return this.internalHost.substr(0,2)==="s-"}isCustomHost(){return this._domain!=="firebaseio.com"&&this._domain!=="firebaseio-demo.com"}get host(){return this._host}set host(e){e!==this.internalHost&&(this.internalHost=e,this.isCacheableHost()&&ke.set("host:"+this._host,this.internalHost))}toString(){let e=this.toURLString();return this.persistenceKey&&(e+="<"+this.persistenceKey+">"),e}toURLString(){const e=this.secure?"https://":"http://",t=this.includeNamespaceInQueryParams?`?ns=${this.namespace}`:"";return`${e}${this.host}/${t}`}}function Kl(n){return n.host!==n.internalHost||n.isCustomHost()||n.includeNamespaceInQueryParams}function Ks(n,e,t){f(typeof e=="string","typeof type must == string"),f(typeof t=="object","typeof params must == object");let i;if(e===js)i=(n.secure?"wss://":"ws://")+n.internalHost+"/.ws?";else if(e===Gs)i=(n.secure?"https://":"http://")+n.internalHost+"/.lp?";else throw new Error("Unknown connection type: "+e);Kl(n)&&(t.ns=n.namespace);const s=[];return O(t,(r,o)=>{s.push(r+"="+o)}),i+s.join("&")}/**
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
 */class Ql{constructor(){this.counters_={}}incrementCounter(e,t=1){J(this.counters_,e)||(this.counters_[e]=0),this.counters_[e]+=t}get(){return Qo(this.counters_)}}/**
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
 */const Vn={},zn={};function jn(n){const e=n.toString();return Vn[e]||(Vn[e]=new Ql),Vn[e]}function Yl(n,e){const t=n.toString();return zn[t]||(zn[t]=e()),zn[t]}/**
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
 */class Jl{constructor(e){this.onMessage_=e,this.pendingResponses=[],this.currentResponseNum=0,this.closeAfterResponse=-1,this.onClose=null}closeAfter(e,t){this.closeAfterResponse=e,this.onClose=t,this.closeAfterResponse<this.currentResponseNum&&(this.onClose(),this.onClose=null)}handleResponse(e,t){for(this.pendingResponses[e]=t;this.pendingResponses[this.currentResponseNum];){const i=this.pendingResponses[this.currentResponseNum];delete this.pendingResponses[this.currentResponseNum];for(let s=0;s<i.length;++s)i[s]&&Ve(()=>{this.onMessage_(i[s])});if(this.currentResponseNum===this.closeAfterResponse){this.onClose&&(this.onClose(),this.onClose=null);break}this.currentResponseNum++}}}/**
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
 */const Qs="start",Xl="close",Zl="pLPCommand",ec="pRTLPCB",Ys="id",Js="pw",Xs="ser",tc="cb",nc="seg",ic="ts",sc="d",rc="dframe",Zs=1870,er=30,oc=Zs-er,ac=25e3,lc=3e4;class _e{constructor(e,t,i,s,r,o,a){this.connId=e,this.repoInfo=t,this.applicationId=i,this.appCheckToken=s,this.authToken=r,this.transportSessionId=o,this.lastSessionId=a,this.bytesSent=0,this.bytesReceived=0,this.everConnected_=!1,this.log_=ot(e),this.stats_=jn(t),this.urlFn=l=>(this.appCheckToken&&(l[Hn]=this.appCheckToken),Ks(t,Gs,l))}open(e,t){this.curSegmentNum=0,this.onDisconnect_=t,this.myPacketOrderer=new Jl(e),this.isClosed_=!1,this.connectTimeoutTimer_=setTimeout(()=>{this.log_("Timed out trying to connect."),this.onClosed_(),this.connectTimeoutTimer_=null},Math.floor(lc)),Fl(()=>{if(this.isClosed_)return;this.scriptTagHolder=new Gn((...r)=>{const[o,a,l,c,d]=r;if(this.incrementIncomingBytes_(r),!!this.scriptTagHolder)if(this.connectTimeoutTimer_&&(clearTimeout(this.connectTimeoutTimer_),this.connectTimeoutTimer_=null),this.everConnected_=!0,o===Qs)this.id=a,this.password=l;else if(o===Xl)a?(this.scriptTagHolder.sendNewPolls=!1,this.myPacketOrderer.closeAfter(a,()=>{this.onClosed_()})):this.onClosed_();else throw new Error("Unrecognized command received: "+o)},(...r)=>{const[o,a]=r;this.incrementIncomingBytes_(r),this.myPacketOrderer.handleResponse(o,a)},()=>{this.onClosed_()},this.urlFn);const i={};i[Qs]="t",i[Xs]=Math.floor(Math.random()*1e8),this.scriptTagHolder.uniqueCallbackIdentifier&&(i[tc]=this.scriptTagHolder.uniqueCallbackIdentifier),i[Bs]=$n,this.transportSessionId&&(i[Ws]=this.transportSessionId),this.lastSessionId&&(i[Vs]=this.lastSessionId),this.applicationId&&(i[zs]=this.applicationId),this.appCheckToken&&(i[Hn]=this.appCheckToken),typeof location<"u"&&location.hostname&&Hs.test(location.hostname)&&(i[Us]=$s);const s=this.urlFn(i);this.log_("Connecting via long-poll to "+s),this.scriptTagHolder.addTag(s,()=>{})})}start(){this.scriptTagHolder.startLongPoll(this.id,this.password),this.addDisconnectPingFrame(this.id,this.password)}static forceAllow(){_e.forceAllow_=!0}static forceDisallow(){_e.forceDisallow_=!0}static isAvailable(){return _e.forceAllow_?!0:!_e.forceDisallow_&&typeof document<"u"&&document.createElement!=null&&!Wl()&&!Ul()}markConnectionHealthy(){}shutdown_(){this.isClosed_=!0,this.scriptTagHolder&&(this.scriptTagHolder.close(),this.scriptTagHolder=null),this.myDisconnFrame&&(document.body.removeChild(this.myDisconnFrame),this.myDisconnFrame=null),this.connectTimeoutTimer_&&(clearTimeout(this.connectTimeoutTimer_),this.connectTimeoutTimer_=null)}onClosed_(){this.isClosed_||(this.log_("Longpoll is closing itself"),this.shutdown_(),this.onDisconnect_&&(this.onDisconnect_(this.everConnected_),this.onDisconnect_=null))}close(){this.isClosed_||(this.log_("Longpoll is being closed."),this.shutdown_())}send(e){const t=P(e);this.bytesSent+=t.length,this.stats_.incrementCounter("bytes_sent",t.length);const i=Xi(t),s=Ms(i,oc);for(let r=0;r<s.length;r++)this.scriptTagHolder.enqueueSegment(this.curSegmentNum,s.length,s[r]),this.curSegmentNum++}addDisconnectPingFrame(e,t){this.myDisconnFrame=document.createElement("iframe");const i={};i[rc]="t",i[Ys]=e,i[Js]=t,this.myDisconnFrame.src=this.urlFn(i),this.myDisconnFrame.style.display="none",document.body.appendChild(this.myDisconnFrame)}incrementIncomingBytes_(e){const t=P(e).length;this.bytesReceived+=t,this.stats_.incrementCounter("bytes_received",t)}}class Gn{constructor(e,t,i,s){this.onDisconnect=i,this.urlFn=s,this.outstandingRequests=new Set,this.pendingSegs=[],this.currentSerial=Math.floor(Math.random()*1e8),this.sendNewPolls=!0;{this.uniqueCallbackIdentifier=Rs(),window[Zl+this.uniqueCallbackIdentifier]=e,window[ec+this.uniqueCallbackIdentifier]=t,this.myIFrame=Gn.createIFrame_();let r="";this.myIFrame.src&&this.myIFrame.src.substr(0,11)==="javascript:"&&(r='<script>document.domain="'+document.domain+'";<\/script>');const o="<html><body>"+r+"</body></html>";try{this.myIFrame.doc.open(),this.myIFrame.doc.write(o),this.myIFrame.doc.close()}catch(a){D("frame writing exception"),a.stack&&D(a.stack),D(a)}}}static createIFrame_(){const e=document.createElement("iframe");if(e.style.display="none",document.body){document.body.appendChild(e);try{e.contentWindow.document||D("No IE domain setting required")}catch{const i=document.domain;e.src="javascript:void((function(){document.open();document.domain='"+i+"';document.close();})())"}}else throw"Document body has not initialized. Wait to initialize Firebase until after the document is ready.";return e.contentDocument?e.doc=e.contentDocument:e.contentWindow?e.doc=e.contentWindow.document:e.document&&(e.doc=e.document),e}close(){this.alive=!1,this.myIFrame&&(this.myIFrame.doc.body.textContent="",setTimeout(()=>{this.myIFrame!==null&&(document.body.removeChild(this.myIFrame),this.myIFrame=null)},Math.floor(0)));const e=this.onDisconnect;e&&(this.onDisconnect=null,e())}startLongPoll(e,t){for(this.myID=e,this.myPW=t,this.alive=!0;this.newRequest_(););}newRequest_(){if(this.alive&&this.sendNewPolls&&this.outstandingRequests.size<(this.pendingSegs.length>0?2:1)){this.currentSerial++;const e={};e[Ys]=this.myID,e[Js]=this.myPW,e[Xs]=this.currentSerial;let t=this.urlFn(e),i="",s=0;for(;this.pendingSegs.length>0&&this.pendingSegs[0].d.length+er+i.length<=Zs;){const o=this.pendingSegs.shift();i=i+"&"+nc+s+"="+o.seg+"&"+ic+s+"="+o.ts+"&"+sc+s+"="+o.d,s++}return t=t+i,this.addLongPollTag_(t,this.currentSerial),!0}else return!1}enqueueSegment(e,t,i){this.pendingSegs.push({seg:e,ts:t,d:i}),this.alive&&this.newRequest_()}addLongPollTag_(e,t){this.outstandingRequests.add(t);const i=()=>{this.outstandingRequests.delete(t),this.newRequest_()},s=setTimeout(i,Math.floor(ac)),r=()=>{clearTimeout(s),i()};this.addTag(e,r)}addTag(e,t){setTimeout(()=>{try{if(!this.sendNewPolls)return;const i=this.myIFrame.doc.createElement("script");i.type="text/javascript",i.async=!0,i.src=e,i.onload=i.onreadystatechange=function(){const s=i.readyState;(!s||s==="loaded"||s==="complete")&&(i.onload=i.onreadystatechange=null,i.parentNode&&i.parentNode.removeChild(i),t())},i.onerror=()=>{D("Long-poll script failed to load: "+e),this.sendNewPolls=!1,this.close()},this.myIFrame.doc.body.appendChild(i)}catch{}},Math.floor(1))}}/**
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
 */const cc=16384,hc=45e3;let Ft=null;typeof MozWebSocket<"u"?Ft=MozWebSocket:typeof WebSocket<"u"&&(Ft=WebSocket);class G{constructor(e,t,i,s,r,o,a){this.connId=e,this.applicationId=i,this.appCheckToken=s,this.authToken=r,this.keepaliveTimer=null,this.frames=null,this.totalFrames=0,this.bytesSent=0,this.bytesReceived=0,this.log_=ot(this.connId),this.stats_=jn(t),this.connURL=G.connectionURL_(t,o,a,s,i),this.nodeAdmin=t.nodeAdmin}static connectionURL_(e,t,i,s,r){const o={};return o[Bs]=$n,typeof location<"u"&&location.hostname&&Hs.test(location.hostname)&&(o[Us]=$s),t&&(o[Ws]=t),i&&(o[Vs]=i),s&&(o[Hn]=s),r&&(o[zs]=r),Ks(e,js,o)}open(e,t){this.onDisconnect=t,this.onMessage=e,this.log_("Websocket connecting to "+this.connURL),this.everConnected_=!1,ke.set("previous_websocket_failure",!0);try{let i;aa(),this.mySock=new Ft(this.connURL,[],i)}catch(i){this.log_("Error instantiating WebSocket.");const s=i.message||i.data;s&&this.log_(s),this.onClosed_();return}this.mySock.onopen=()=>{this.log_("Websocket connected."),this.everConnected_=!0},this.mySock.onclose=()=>{this.log_("Websocket connection was disconnected."),this.mySock=null,this.onClosed_()},this.mySock.onmessage=i=>{this.handleIncomingFrame(i)},this.mySock.onerror=i=>{this.log_("WebSocket error.  Closing connection.");const s=i.message||i.data;s&&this.log_(s),this.onClosed_()}}start(){}static forceDisallow(){G.forceDisallow_=!0}static isAvailable(){let e=!1;if(typeof navigator<"u"&&navigator.userAgent){const t=/Android ([0-9]{0,}\.[0-9]{0,})/,i=navigator.userAgent.match(t);i&&i.length>1&&parseFloat(i[1])<4.4&&(e=!0)}return!e&&Ft!==null&&!G.forceDisallow_}static previouslyFailed(){return ke.isInMemoryStorage||ke.get("previous_websocket_failure")===!0}markConnectionHealthy(){ke.remove("previous_websocket_failure")}appendFrame_(e){if(this.frames.push(e),this.frames.length===this.totalFrames){const t=this.frames.join("");this.frames=null;const i=tt(t);this.onMessage(i)}}handleNewFrameCount_(e){this.totalFrames=e,this.frames=[]}extractFrameCount_(e){if(f(this.frames===null,"We already have a frame buffer"),e.length<=6){const t=Number(e);if(!isNaN(t))return this.handleNewFrameCount_(t),null}return this.handleNewFrameCount_(1),e}handleIncomingFrame(e){if(this.mySock===null)return;const t=e.data;if(this.bytesReceived+=t.length,this.stats_.incrementCounter("bytes_received",t.length),this.resetKeepAlive(),this.frames!==null)this.appendFrame_(t);else{const i=this.extractFrameCount_(t);i!==null&&this.appendFrame_(i)}}send(e){this.resetKeepAlive();const t=P(e);this.bytesSent+=t.length,this.stats_.incrementCounter("bytes_sent",t.length);const i=Ms(t,cc);i.length>1&&this.sendString_(String(i.length));for(let s=0;s<i.length;s++)this.sendString_(i[s])}shutdown_(){this.isClosed_=!0,this.keepaliveTimer&&(clearInterval(this.keepaliveTimer),this.keepaliveTimer=null),this.mySock&&(this.mySock.close(),this.mySock=null)}onClosed_(){this.isClosed_||(this.log_("WebSocket is closing itself"),this.shutdown_(),this.onDisconnect&&(this.onDisconnect(this.everConnected_),this.onDisconnect=null))}close(){this.isClosed_||(this.log_("WebSocket is being closed"),this.shutdown_())}resetKeepAlive(){clearInterval(this.keepaliveTimer),this.keepaliveTimer=setInterval(()=>{this.mySock&&this.sendString_("0"),this.resetKeepAlive()},Math.floor(hc))}sendString_(e){try{this.mySock.send(e)}catch(t){this.log_("Exception thrown from WebSocket.send():",t.message||t.data,"Closing connection."),setTimeout(this.onClosed_.bind(this),0)}}}G.responsesRequiredToBeHealthy=2,G.healthyTimeout=3e4;/**
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
 */class je{static get ALL_TRANSPORTS(){return[_e,G]}static get IS_TRANSPORT_INITIALIZED(){return this.globalTransportInitialized_}constructor(e){this.initTransports_(e)}initTransports_(e){const t=G&&G.isAvailable();let i=t&&!G.previouslyFailed();if(e.webSocketOnly&&(t||B("wss:// URL used, but browser isn't known to support websockets.  Trying anyway."),i=!0),i)this.transports_=[G];else{const s=this.transports_=[];for(const r of je.ALL_TRANSPORTS)r&&r.isAvailable()&&s.push(r);je.globalTransportInitialized_=!0}}initialTransport(){if(this.transports_.length>0)return this.transports_[0];throw new Error("No transports available")}upgradeTransport(){return this.transports_.length>1?this.transports_[1]:null}}je.globalTransportInitialized_=!1;/**
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
 */const dc=6e4,uc=5e3,fc=10*1024,pc=100*1024,qn="t",tr="d",_c="s",nr="r",mc="e",ir="o",sr="a",rr="n",or="p",gc="h";class yc{constructor(e,t,i,s,r,o,a,l,c,d){this.id=e,this.repoInfo_=t,this.applicationId_=i,this.appCheckToken_=s,this.authToken_=r,this.onMessage_=o,this.onReady_=a,this.onDisconnect_=l,this.onKill_=c,this.lastSessionId=d,this.connectionCount=0,this.pendingDataMessages=[],this.state_=0,this.log_=ot("c:"+this.id+":"),this.transportManager_=new je(t),this.log_("Connection created"),this.start_()}start_(){const e=this.transportManager_.initialTransport();this.conn_=new e(this.nextTransportId_(),this.repoInfo_,this.applicationId_,this.appCheckToken_,this.authToken_,null,this.lastSessionId),this.primaryResponsesRequired_=e.responsesRequiredToBeHealthy||0;const t=this.connReceiver_(this.conn_),i=this.disconnReceiver_(this.conn_);this.tx_=this.conn_,this.rx_=this.conn_,this.secondaryConn_=null,this.isHealthy_=!1,setTimeout(()=>{this.conn_&&this.conn_.open(t,i)},Math.floor(0));const s=e.healthyTimeout||0;s>0&&(this.healthyTimeout_=lt(()=>{this.healthyTimeout_=null,this.isHealthy_||(this.conn_&&this.conn_.bytesReceived>pc?(this.log_("Connection exceeded healthy timeout but has received "+this.conn_.bytesReceived+" bytes.  Marking connection healthy."),this.isHealthy_=!0,this.conn_.markConnectionHealthy()):this.conn_&&this.conn_.bytesSent>fc?this.log_("Connection exceeded healthy timeout but has sent "+this.conn_.bytesSent+" bytes.  Leaving connection alive."):(this.log_("Closing unhealthy connection after timeout."),this.close()))},Math.floor(s)))}nextTransportId_(){return"c:"+this.id+":"+this.connectionCount++}disconnReceiver_(e){return t=>{e===this.conn_?this.onConnectionLost_(t):e===this.secondaryConn_?(this.log_("Secondary connection lost."),this.onSecondaryConnectionLost_()):this.log_("closing an old connection")}}connReceiver_(e){return t=>{this.state_!==2&&(e===this.rx_?this.onPrimaryMessageReceived_(t):e===this.secondaryConn_?this.onSecondaryMessageReceived_(t):this.log_("message on old connection"))}}sendRequest(e){const t={t:"d",d:e};this.sendData_(t)}tryCleanupConnection(){this.tx_===this.secondaryConn_&&this.rx_===this.secondaryConn_&&(this.log_("cleaning up and promoting a connection: "+this.secondaryConn_.connId),this.conn_=this.secondaryConn_,this.secondaryConn_=null)}onSecondaryControl_(e){if(qn in e){const t=e[qn];t===sr?this.upgradeIfSecondaryHealthy_():t===nr?(this.log_("Got a reset on secondary, closing it"),this.secondaryConn_.close(),(this.tx_===this.secondaryConn_||this.rx_===this.secondaryConn_)&&this.close()):t===ir&&(this.log_("got pong on secondary."),this.secondaryResponsesRequired_--,this.upgradeIfSecondaryHealthy_())}}onSecondaryMessageReceived_(e){const t=at("t",e),i=at("d",e);if(t==="c")this.onSecondaryControl_(i);else if(t==="d")this.pendingDataMessages.push(i);else throw new Error("Unknown protocol layer: "+t)}upgradeIfSecondaryHealthy_(){this.secondaryResponsesRequired_<=0?(this.log_("Secondary connection is healthy."),this.isHealthy_=!0,this.secondaryConn_.markConnectionHealthy(),this.proceedWithUpgrade_()):(this.log_("sending ping on secondary."),this.secondaryConn_.send({t:"c",d:{t:or,d:{}}}))}proceedWithUpgrade_(){this.secondaryConn_.start(),this.log_("sending client ack on secondary"),this.secondaryConn_.send({t:"c",d:{t:sr,d:{}}}),this.log_("Ending transmission on primary"),this.conn_.send({t:"c",d:{t:rr,d:{}}}),this.tx_=this.secondaryConn_,this.tryCleanupConnection()}onPrimaryMessageReceived_(e){const t=at("t",e),i=at("d",e);t==="c"?this.onControl_(i):t==="d"&&this.onDataMessage_(i)}onDataMessage_(e){this.onPrimaryResponse_(),this.onMessage_(e)}onPrimaryResponse_(){this.isHealthy_||(this.primaryResponsesRequired_--,this.primaryResponsesRequired_<=0&&(this.log_("Primary connection is healthy."),this.isHealthy_=!0,this.conn_.markConnectionHealthy()))}onControl_(e){const t=at(qn,e);if(tr in e){const i=e[tr];if(t===gc){const s={...i};this.repoInfo_.isUsingEmulator&&(s.h=this.repoInfo_.host),this.onHandshake_(s)}else if(t===rr){this.log_("recvd end transmission on primary"),this.rx_=this.secondaryConn_;for(let s=0;s<this.pendingDataMessages.length;++s)this.onDataMessage_(this.pendingDataMessages[s]);this.pendingDataMessages=[],this.tryCleanupConnection()}else t===_c?this.onConnectionShutdown_(i):t===nr?this.onReset_(i):t===mc?Wn("Server Error: "+i):t===ir?(this.log_("got pong on primary."),this.onPrimaryResponse_(),this.sendPingOnPrimaryIfNecessary_()):Wn("Unknown control packet command: "+t)}}onHandshake_(e){const t=e.ts,i=e.v,s=e.h;this.sessionId=e.s,this.repoInfo_.host=s,this.state_===0&&(this.conn_.start(),this.onConnectionEstablished_(this.conn_,t),$n!==i&&B("Protocol version mismatch detected"),this.tryStartUpgrade_())}tryStartUpgrade_(){const e=this.transportManager_.upgradeTransport();e&&this.startUpgrade_(e)}startUpgrade_(e){this.secondaryConn_=new e(this.nextTransportId_(),this.repoInfo_,this.applicationId_,this.appCheckToken_,this.authToken_,this.sessionId),this.secondaryResponsesRequired_=e.responsesRequiredToBeHealthy||0;const t=this.connReceiver_(this.secondaryConn_),i=this.disconnReceiver_(this.secondaryConn_);this.secondaryConn_.open(t,i),lt(()=>{this.secondaryConn_&&(this.log_("Timed out trying to upgrade."),this.secondaryConn_.close())},Math.floor(dc))}onReset_(e){this.log_("Reset packet received.  New host: "+e),this.repoInfo_.host=e,this.state_===1?this.close():(this.closeConnections_(),this.start_())}onConnectionEstablished_(e,t){this.log_("Realtime connection established."),this.conn_=e,this.state_=1,this.onReady_&&(this.onReady_(t,this.sessionId),this.onReady_=null),this.primaryResponsesRequired_===0?(this.log_("Primary connection is healthy."),this.isHealthy_=!0):lt(()=>{this.sendPingOnPrimaryIfNecessary_()},Math.floor(uc))}sendPingOnPrimaryIfNecessary_(){!this.isHealthy_&&this.state_===1&&(this.log_("sending ping on primary."),this.sendData_({t:"c",d:{t:or,d:{}}}))}onSecondaryConnectionLost_(){const e=this.secondaryConn_;this.secondaryConn_=null,(this.tx_===e||this.rx_===e)&&this.close()}onConnectionLost_(e){this.conn_=null,!e&&this.state_===0?(this.log_("Realtime connection failed."),this.repoInfo_.isCacheableHost()&&(ke.remove("host:"+this.repoInfo_.host),this.repoInfo_.internalHost=this.repoInfo_.host)):this.state_===1&&this.log_("Realtime connection lost."),this.close()}onConnectionShutdown_(e){this.log_("Connection shutdown command received. Shutting down..."),this.onKill_&&(this.onKill_(e),this.onKill_=null),this.onDisconnect_=null,this.close()}sendData_(e){if(this.state_!==1)throw"Connection is not connected";this.tx_.send(e)}close(){this.state_!==2&&(this.log_("Closing realtime connection."),this.state_=2,this.closeConnections_(),this.onDisconnect_&&(this.onDisconnect_(),this.onDisconnect_=null))}closeConnections_(){this.log_("Shutting down all connections"),this.conn_&&(this.conn_.close(),this.conn_=null),this.secondaryConn_&&(this.secondaryConn_.close(),this.secondaryConn_=null),this.healthyTimeout_&&(clearTimeout(this.healthyTimeout_),this.healthyTimeout_=null)}}/**
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
 */class ar{put(e,t,i,s){}merge(e,t,i,s){}refreshAuthToken(e){}refreshAppCheckToken(e){}onDisconnectPut(e,t,i){}onDisconnectMerge(e,t,i){}onDisconnectCancel(e,t){}reportStats(e){}}/**
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
 */class lr{constructor(e){this.allowedEvents_=e,this.listeners_={},f(Array.isArray(e)&&e.length>0,"Requires a non-empty array")}trigger(e,...t){if(Array.isArray(this.listeners_[e])){const i=[...this.listeners_[e]];for(let s=0;s<i.length;s++)i[s].callback.apply(i[s].context,t)}}on(e,t,i){this.validateEventType_(e),this.listeners_[e]=this.listeners_[e]||[],this.listeners_[e].push({callback:t,context:i});const s=this.getInitialEvent(e);s&&t.apply(i,s)}off(e,t,i){this.validateEventType_(e);const s=this.listeners_[e]||[];for(let r=0;r<s.length;r++)if(s[r].callback===t&&(!i||i===s[r].context)){s.splice(r,1);return}}validateEventType_(e){f(this.allowedEvents_.find(t=>t===e),"Unknown event: "+e)}}/**
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
 */class Bt extends lr{static getInstance(){return new Bt}constructor(){super(["online"]),this.online_=!0,typeof window<"u"&&typeof window.addEventListener<"u"&&!ts()&&(window.addEventListener("online",()=>{this.online_||(this.online_=!0,this.trigger("online",!0))},!1),window.addEventListener("offline",()=>{this.online_&&(this.online_=!1,this.trigger("online",!1))},!1))}getInitialEvent(e){return f(e==="online","Unknown event type: "+e),[this.online_]}currentlyOnline(){return this.online_}}/**
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
 */const cr=32,hr=768;class E{constructor(e,t){if(t===void 0){this.pieces_=e.split("/");let i=0;for(let s=0;s<this.pieces_.length;s++)this.pieces_[s].length>0&&(this.pieces_[i]=this.pieces_[s],i++);this.pieces_.length=i,this.pieceNum_=0}else this.pieces_=e,this.pieceNum_=t}toString(){let e="";for(let t=this.pieceNum_;t<this.pieces_.length;t++)this.pieces_[t]!==""&&(e+="/"+this.pieces_[t]);return e||"/"}}function w(){return new E("")}function y(n){return n.pieceNum_>=n.pieces_.length?null:n.pieces_[n.pieceNum_]}function me(n){return n.pieces_.length-n.pieceNum_}function x(n){let e=n.pieceNum_;return e<n.pieces_.length&&e++,new E(n.pieces_,e)}function Kn(n){return n.pieceNum_<n.pieces_.length?n.pieces_[n.pieces_.length-1]:null}function vc(n){let e="";for(let t=n.pieceNum_;t<n.pieces_.length;t++)n.pieces_[t]!==""&&(e+="/"+encodeURIComponent(String(n.pieces_[t])));return e||"/"}function ct(n,e=0){return n.pieces_.slice(n.pieceNum_+e)}function dr(n){if(n.pieceNum_>=n.pieces_.length)return null;const e=[];for(let t=n.pieceNum_;t<n.pieces_.length-1;t++)e.push(n.pieces_[t]);return new E(e,0)}function k(n,e){const t=[];for(let i=n.pieceNum_;i<n.pieces_.length;i++)t.push(n.pieces_[i]);if(e instanceof E)for(let i=e.pieceNum_;i<e.pieces_.length;i++)t.push(e.pieces_[i]);else{const i=e.split("/");for(let s=0;s<i.length;s++)i[s].length>0&&t.push(i[s])}return new E(t,0)}function v(n){return n.pieceNum_>=n.pieces_.length}function W(n,e){const t=y(n),i=y(e);if(t===null)return e;if(t===i)return W(x(n),x(e));throw new Error("INTERNAL ERROR: innerPath ("+e+") is not within outerPath ("+n+")")}function bc(n,e){const t=ct(n,0),i=ct(e,0);for(let s=0;s<t.length&&s<i.length;s++){const r=Re(t[s],i[s]);if(r!==0)return r}return t.length===i.length?0:t.length<i.length?-1:1}function Qn(n,e){if(me(n)!==me(e))return!1;for(let t=n.pieceNum_,i=e.pieceNum_;t<=n.pieces_.length;t++,i++)if(n.pieces_[t]!==e.pieces_[i])return!1;return!0}function q(n,e){let t=n.pieceNum_,i=e.pieceNum_;if(me(n)>me(e))return!1;for(;t<n.pieces_.length;){if(n.pieces_[t]!==e.pieces_[i])return!1;++t,++i}return!0}class wc{constructor(e,t){this.errorPrefix_=t,this.parts_=ct(e,0),this.byteLength_=Math.max(1,this.parts_.length);for(let i=0;i<this.parts_.length;i++)this.byteLength_+=Ot(this.parts_[i]);ur(this)}}function Cc(n,e){n.parts_.length>0&&(n.byteLength_+=1),n.parts_.push(e),n.byteLength_+=Ot(e),ur(n)}function Ec(n){const e=n.parts_.pop();n.byteLength_-=Ot(e),n.parts_.length>0&&(n.byteLength_-=1)}function ur(n){if(n.byteLength_>hr)throw new Error(n.errorPrefix_+"has a key path longer than "+hr+" bytes ("+n.byteLength_+").");if(n.parts_.length>cr)throw new Error(n.errorPrefix_+"path specified exceeds the maximum depth that can be written ("+cr+") or object contains a cycle "+Pe(n))}function Pe(n){return n.parts_.length===0?"":"in property '"+n.parts_.join(".")+"'"}/**
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
 */class Yn extends lr{static getInstance(){return new Yn}constructor(){super(["visible"]);let e,t;typeof document<"u"&&typeof document.addEventListener<"u"&&(typeof document.hidden<"u"?(t="visibilitychange",e="hidden"):typeof document.mozHidden<"u"?(t="mozvisibilitychange",e="mozHidden"):typeof document.msHidden<"u"?(t="msvisibilitychange",e="msHidden"):typeof document.webkitHidden<"u"&&(t="webkitvisibilitychange",e="webkitHidden")),this.visible_=!0,t&&document.addEventListener(t,()=>{const i=!document[e];i!==this.visible_&&(this.visible_=i,this.trigger("visible",i))},!1)}getInitialEvent(e){return f(e==="visible","Unknown event type: "+e),[this.visible_]}}/**
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
 */const ht=1e3,Sc=300*1e3,fr=30*1e3,Ic=1.3,xc=3e4,Tc="server_kill",pr=3;class K extends ar{constructor(e,t,i,s,r,o,a,l){if(super(),this.repoInfo_=e,this.applicationId_=t,this.onDataUpdate_=i,this.onConnectStatus_=s,this.onServerInfoUpdate_=r,this.authTokenProvider_=o,this.appCheckTokenProvider_=a,this.authOverride_=l,this.id=K.nextPersistentConnectionId_++,this.log_=ot("p:"+this.id+":"),this.interruptReasons_={},this.listens=new Map,this.outstandingPuts_=[],this.outstandingGets_=[],this.outstandingPutCount_=0,this.outstandingGetCount_=0,this.onDisconnectRequestQueue_=[],this.connected_=!1,this.reconnectDelay_=ht,this.maxReconnectDelay_=Sc,this.securityDebugCallback_=null,this.lastSessionId=null,this.establishConnectionTimer_=null,this.visible_=!1,this.requestCBHash_={},this.requestNumber_=0,this.realtime_=null,this.authToken_=null,this.appCheckToken_=null,this.forceTokenRefresh_=!1,this.invalidAuthTokenCount_=0,this.invalidAppCheckTokenCount_=0,this.firstConnection_=!0,this.lastConnectionAttemptTime_=null,this.lastConnectionEstablishedTime_=null,l)throw new Error("Auth override specified in options, but not supported on non Node.js platforms");Yn.getInstance().on("visible",this.onVisible_,this),e.host.indexOf("fblocal")===-1&&Bt.getInstance().on("online",this.onOnline_,this)}sendRequest(e,t,i){const s=++this.requestNumber_,r={r:s,a:e,b:t};this.log_(P(r)),f(this.connected_,"sendRequest call when we're not connected not allowed."),this.realtime_.sendRequest(r),i&&(this.requestCBHash_[s]=i)}get(e){this.initConnection_();const t=new z,s={action:"g",request:{p:e._path.toString(),q:e._queryObject},onComplete:o=>{const a=o.d;o.s==="ok"?t.resolve(a):t.reject(a)}};this.outstandingGets_.push(s),this.outstandingGetCount_++;const r=this.outstandingGets_.length-1;return this.connected_&&this.sendGet_(r),t.promise}listen(e,t,i,s){this.initConnection_();const r=e._queryIdentifier,o=e._path.toString();this.log_("Listen called for "+o+" "+r),this.listens.has(o)||this.listens.set(o,new Map),f(e._queryParams.isDefault()||!e._queryParams.loadsAllData(),"listen() called for non-default but complete query"),f(!this.listens.get(o).has(r),"listen() called twice for same path/queryId.");const a={onComplete:s,hashFn:t,query:e,tag:i};this.listens.get(o).set(r,a),this.connected_&&this.sendListen_(a)}sendGet_(e){const t=this.outstandingGets_[e];this.sendRequest("g",t.request,i=>{delete this.outstandingGets_[e],this.outstandingGetCount_--,this.outstandingGetCount_===0&&(this.outstandingGets_=[]),t.onComplete&&t.onComplete(i)})}sendListen_(e){const t=e.query,i=t._path.toString(),s=t._queryIdentifier;this.log_("Listen on "+i+" for "+s);const r={p:i},o="q";e.tag&&(r.q=t._queryObject,r.t=e.tag),r.h=e.hashFn(),this.sendRequest(o,r,a=>{const l=a.d,c=a.s;K.warnOnListenWarnings_(l,t),(this.listens.get(i)&&this.listens.get(i).get(s))===e&&(this.log_("listen response",a),c!=="ok"&&this.removeListen_(i,s),e.onComplete&&e.onComplete(c,l))})}static warnOnListenWarnings_(e,t){if(e&&typeof e=="object"&&J(e,"w")){const i=Ie(e,"w");if(Array.isArray(i)&&~i.indexOf("no_index")){const s='".indexOn": "'+t._queryParams.getIndex().toString()+'"',r=t._path.toString();B(`Using an unspecified index. Your data will be downloaded and filtered on the client. Consider adding ${s} at ${r} to your security rules for better performance.`)}}}refreshAuthToken(e){this.authToken_=e,this.log_("Auth token refreshed"),this.authToken_?this.tryAuth():this.connected_&&this.sendRequest("unauth",{},()=>{}),this.reduceReconnectDelayIfAdminCredential_(e)}reduceReconnectDelayIfAdminCredential_(e){(e&&e.length===40||fa(e))&&(this.log_("Admin auth credential detected.  Reducing max reconnect time."),this.maxReconnectDelay_=fr)}refreshAppCheckToken(e){this.appCheckToken_=e,this.log_("App check token refreshed"),this.appCheckToken_?this.tryAppCheck():this.connected_&&this.sendRequest("unappeck",{},()=>{})}tryAuth(){if(this.connected_&&this.authToken_){const e=this.authToken_,t=ua(e)?"auth":"gauth",i={cred:e};this.authOverride_===null?i.noauth=!0:typeof this.authOverride_=="object"&&(i.authvar=this.authOverride_),this.sendRequest(t,i,s=>{const r=s.s,o=s.d||"error";this.authToken_===e&&(r==="ok"?this.invalidAuthTokenCount_=0:this.onAuthRevoked_(r,o))})}}tryAppCheck(){this.connected_&&this.appCheckToken_&&this.sendRequest("appcheck",{token:this.appCheckToken_},e=>{const t=e.s,i=e.d||"error";t==="ok"?this.invalidAppCheckTokenCount_=0:this.onAppCheckRevoked_(t,i)})}unlisten(e,t){const i=e._path.toString(),s=e._queryIdentifier;this.log_("Unlisten called for "+i+" "+s),f(e._queryParams.isDefault()||!e._queryParams.loadsAllData(),"unlisten() called for non-default but complete query"),this.removeListen_(i,s)&&this.connected_&&this.sendUnlisten_(i,s,e._queryObject,t)}sendUnlisten_(e,t,i,s){this.log_("Unlisten on "+e+" for "+t);const r={p:e},o="n";s&&(r.q=i,r.t=s),this.sendRequest(o,r)}onDisconnectPut(e,t,i){this.initConnection_(),this.connected_?this.sendOnDisconnect_("o",e,t,i):this.onDisconnectRequestQueue_.push({pathString:e,action:"o",data:t,onComplete:i})}onDisconnectMerge(e,t,i){this.initConnection_(),this.connected_?this.sendOnDisconnect_("om",e,t,i):this.onDisconnectRequestQueue_.push({pathString:e,action:"om",data:t,onComplete:i})}onDisconnectCancel(e,t){this.initConnection_(),this.connected_?this.sendOnDisconnect_("oc",e,null,t):this.onDisconnectRequestQueue_.push({pathString:e,action:"oc",data:null,onComplete:t})}sendOnDisconnect_(e,t,i,s){const r={p:t,d:i};this.log_("onDisconnect "+e,r),this.sendRequest(e,r,o=>{s&&setTimeout(()=>{s(o.s,o.d)},Math.floor(0))})}put(e,t,i,s){this.putInternal("p",e,t,i,s)}merge(e,t,i,s){this.putInternal("m",e,t,i,s)}putInternal(e,t,i,s,r){this.initConnection_();const o={p:t,d:i};r!==void 0&&(o.h=r),this.outstandingPuts_.push({action:e,request:o,onComplete:s}),this.outstandingPutCount_++;const a=this.outstandingPuts_.length-1;this.connected_?this.sendPut_(a):this.log_("Buffering put: "+t)}sendPut_(e){const t=this.outstandingPuts_[e].action,i=this.outstandingPuts_[e].request,s=this.outstandingPuts_[e].onComplete;this.outstandingPuts_[e].queued=this.connected_,this.sendRequest(t,i,r=>{this.log_(t+" response",r),delete this.outstandingPuts_[e],this.outstandingPutCount_--,this.outstandingPutCount_===0&&(this.outstandingPuts_=[]),s&&s(r.s,r.d)})}reportStats(e){if(this.connected_){const t={c:e};this.log_("reportStats",t),this.sendRequest("s",t,i=>{if(i.s!=="ok"){const r=i.d;this.log_("reportStats","Error sending stats: "+r)}})}}onDataMessage_(e){if("r"in e){this.log_("from server: "+P(e));const t=e.r,i=this.requestCBHash_[t];i&&(delete this.requestCBHash_[t],i(e.b))}else{if("error"in e)throw"A server-side error has occurred: "+e.error;"a"in e&&this.onDataPush_(e.a,e.b)}}onDataPush_(e,t){this.log_("handleServerMessage",e,t),e==="d"?this.onDataUpdate_(t.p,t.d,!1,t.t):e==="m"?this.onDataUpdate_(t.p,t.d,!0,t.t):e==="c"?this.onListenRevoked_(t.p,t.q):e==="ac"?this.onAuthRevoked_(t.s,t.d):e==="apc"?this.onAppCheckRevoked_(t.s,t.d):e==="sd"?this.onSecurityDebugPacket_(t):Wn("Unrecognized action received from server: "+P(e)+`
Are you using the latest client?`)}onReady_(e,t){this.log_("connection ready"),this.connected_=!0,this.lastConnectionEstablishedTime_=new Date().getTime(),this.handleTimestamp_(e),this.lastSessionId=t,this.firstConnection_&&this.sendConnectStats_(),this.restoreState_(),this.firstConnection_=!1,this.onConnectStatus_(!0)}scheduleConnect_(e){f(!this.realtime_,"Scheduling a connect when we're already connected/ing?"),this.establishConnectionTimer_&&clearTimeout(this.establishConnectionTimer_),this.establishConnectionTimer_=setTimeout(()=>{this.establishConnectionTimer_=null,this.establishConnection_()},Math.floor(e))}initConnection_(){!this.realtime_&&this.firstConnection_&&this.scheduleConnect_(0)}onVisible_(e){e&&!this.visible_&&this.reconnectDelay_===this.maxReconnectDelay_&&(this.log_("Window became visible.  Reducing delay."),this.reconnectDelay_=ht,this.realtime_||this.scheduleConnect_(0)),this.visible_=e}onOnline_(e){e?(this.log_("Browser went online."),this.reconnectDelay_=ht,this.realtime_||this.scheduleConnect_(0)):(this.log_("Browser went offline.  Killing connection."),this.realtime_&&this.realtime_.close())}onRealtimeDisconnect_(){if(this.log_("data client disconnected"),this.connected_=!1,this.realtime_=null,this.cancelSentTransactions_(),this.requestCBHash_={},this.shouldReconnect_()){this.visible_?this.lastConnectionEstablishedTime_&&(new Date().getTime()-this.lastConnectionEstablishedTime_>xc&&(this.reconnectDelay_=ht),this.lastConnectionEstablishedTime_=null):(this.log_("Window isn't visible.  Delaying reconnect."),this.reconnectDelay_=this.maxReconnectDelay_,this.lastConnectionAttemptTime_=new Date().getTime());const e=Math.max(0,new Date().getTime()-this.lastConnectionAttemptTime_);let t=Math.max(0,this.reconnectDelay_-e);t=Math.random()*t,this.log_("Trying to reconnect in "+t+"ms"),this.scheduleConnect_(t),this.reconnectDelay_=Math.min(this.maxReconnectDelay_,this.reconnectDelay_*Ic)}this.onConnectStatus_(!1)}async establishConnection_(){if(this.shouldReconnect_()){this.log_("Making a connection attempt"),this.lastConnectionAttemptTime_=new Date().getTime(),this.lastConnectionEstablishedTime_=null;const e=this.onDataMessage_.bind(this),t=this.onReady_.bind(this),i=this.onRealtimeDisconnect_.bind(this),s=this.id+":"+K.nextConnectionId_++,r=this.lastSessionId;let o=!1,a=null;const l=function(){a?a.close():(o=!0,i())},c=function(h){f(a,"sendRequest call when we're not connected not allowed."),a.sendRequest(h)};this.realtime_={close:l,sendRequest:c};const d=this.forceTokenRefresh_;this.forceTokenRefresh_=!1;try{const[h,u]=await Promise.all([this.authTokenProvider_.getToken(d),this.appCheckTokenProvider_.getToken(d)]);o?D("getToken() completed but was canceled"):(D("getToken() completed. Creating connection."),this.authToken_=h&&h.accessToken,this.appCheckToken_=u&&u.token,a=new yc(s,this.repoInfo_,this.applicationId_,this.appCheckToken_,this.authToken_,e,t,i,p=>{B(p+" ("+this.repoInfo_.toString()+")"),this.interrupt(Tc)},r))}catch(h){this.log_("Failed to get token: "+h),o||(this.repoInfo_.nodeAdmin&&B(h),l())}}}interrupt(e){D("Interrupting connection for reason: "+e),this.interruptReasons_[e]=!0,this.realtime_?this.realtime_.close():(this.establishConnectionTimer_&&(clearTimeout(this.establishConnectionTimer_),this.establishConnectionTimer_=null),this.connected_&&this.onRealtimeDisconnect_())}resume(e){D("Resuming connection for reason: "+e),delete this.interruptReasons_[e],Cn(this.interruptReasons_)&&(this.reconnectDelay_=ht,this.realtime_||this.scheduleConnect_(0))}handleTimestamp_(e){const t=e-new Date().getTime();this.onServerInfoUpdate_({serverTimeOffset:t})}cancelSentTransactions_(){for(let e=0;e<this.outstandingPuts_.length;e++){const t=this.outstandingPuts_[e];t&&"h"in t.request&&t.queued&&(t.onComplete&&t.onComplete("disconnect"),delete this.outstandingPuts_[e],this.outstandingPutCount_--)}this.outstandingPutCount_===0&&(this.outstandingPuts_=[])}onListenRevoked_(e,t){let i;t?i=t.map(r=>Un(r)).join("$"):i="default";const s=this.removeListen_(e,i);s&&s.onComplete&&s.onComplete("permission_denied")}removeListen_(e,t){const i=new E(e).toString();let s;if(this.listens.has(i)){const r=this.listens.get(i);s=r.get(t),r.delete(t),r.size===0&&this.listens.delete(i)}else s=void 0;return s}onAuthRevoked_(e,t){D("Auth token revoked: "+e+"/"+t),this.authToken_=null,this.forceTokenRefresh_=!0,this.realtime_.close(),(e==="invalid_token"||e==="permission_denied")&&(this.invalidAuthTokenCount_++,this.invalidAuthTokenCount_>=pr&&(this.reconnectDelay_=fr,this.authTokenProvider_.notifyForInvalidToken()))}onAppCheckRevoked_(e,t){D("App check token revoked: "+e+"/"+t),this.appCheckToken_=null,this.forceTokenRefresh_=!0,(e==="invalid_token"||e==="permission_denied")&&(this.invalidAppCheckTokenCount_++,this.invalidAppCheckTokenCount_>=pr&&this.appCheckTokenProvider_.notifyForInvalidToken())}onSecurityDebugPacket_(e){this.securityDebugCallback_?this.securityDebugCallback_(e):"msg"in e&&console.log("FIREBASE: "+e.msg.replace(`
`,`
FIREBASE: `))}restoreState_(){this.tryAuth(),this.tryAppCheck();for(const e of this.listens.values())for(const t of e.values())this.sendListen_(t);for(let e=0;e<this.outstandingPuts_.length;e++)this.outstandingPuts_[e]&&this.sendPut_(e);for(;this.onDisconnectRequestQueue_.length;){const e=this.onDisconnectRequestQueue_.shift();this.sendOnDisconnect_(e.action,e.pathString,e.data,e.onComplete)}for(let e=0;e<this.outstandingGets_.length;e++)this.outstandingGets_[e]&&this.sendGet_(e)}sendConnectStats_(){const e={};let t="js";e["sdk."+t+"."+ks.replace(/\./g,"-")]=1,ts()?e["framework.cordova"]=1:oa()&&(e["framework.reactnative"]=1),this.reportStats(e)}shouldReconnect_(){const e=Bt.getInstance().currentlyOnline();return Cn(this.interruptReasons_)&&e}}K.nextPersistentConnectionId_=0,K.nextConnectionId_=0;/**
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
 */class Wt{getCompare(){return this.compare.bind(this)}indexedValueChanged(e,t){const i=new b(pe,e),s=new b(pe,t);return this.compare(i,s)!==0}minPost(){return b.MIN}}/**
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
 */let Ut;class _r extends Wt{static get __EMPTY_NODE(){return Ut}static set __EMPTY_NODE(e){Ut=e}compare(e,t){return Re(e.name,t.name)}isDefinedOn(e){throw Be("KeyIndex.isDefinedOn not expected to be called.")}indexedValueChanged(e,t){return!1}minPost(){return b.MIN}maxPost(){return new b(ce,Ut)}makePost(e,t){return f(typeof e=="string","KeyIndex indexValue must always be a string."),new b(e,Ut)}toString(){return".key"}}const ne=new _r;/**
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
 */class $t{constructor(e,t,i,s,r=null){this.isReverse_=s,this.resultGenerator_=r,this.nodeStack_=[];let o=1;for(;!e.isEmpty();)if(e=e,o=t?i(e.key,t):1,s&&(o*=-1),o<0)this.isReverse_?e=e.left:e=e.right;else if(o===0){this.nodeStack_.push(e);break}else this.nodeStack_.push(e),this.isReverse_?e=e.right:e=e.left}getNext(){if(this.nodeStack_.length===0)return null;let e=this.nodeStack_.pop(),t;if(this.resultGenerator_?t=this.resultGenerator_(e.key,e.value):t={key:e.key,value:e.value},this.isReverse_)for(e=e.left;!e.isEmpty();)this.nodeStack_.push(e),e=e.right;else for(e=e.right;!e.isEmpty();)this.nodeStack_.push(e),e=e.left;return t}hasNext(){return this.nodeStack_.length>0}peek(){if(this.nodeStack_.length===0)return null;const e=this.nodeStack_[this.nodeStack_.length-1];return this.resultGenerator_?this.resultGenerator_(e.key,e.value):{key:e.key,value:e.value}}}class M{constructor(e,t,i,s,r){this.key=e,this.value=t,this.color=i??M.RED,this.left=s??H.EMPTY_NODE,this.right=r??H.EMPTY_NODE}copy(e,t,i,s,r){return new M(e??this.key,t??this.value,i??this.color,s??this.left,r??this.right)}count(){return this.left.count()+1+this.right.count()}isEmpty(){return!1}inorderTraversal(e){return this.left.inorderTraversal(e)||!!e(this.key,this.value)||this.right.inorderTraversal(e)}reverseTraversal(e){return this.right.reverseTraversal(e)||e(this.key,this.value)||this.left.reverseTraversal(e)}min_(){return this.left.isEmpty()?this:this.left.min_()}minKey(){return this.min_().key}maxKey(){return this.right.isEmpty()?this.key:this.right.maxKey()}insert(e,t,i){let s=this;const r=i(e,s.key);return r<0?s=s.copy(null,null,null,s.left.insert(e,t,i),null):r===0?s=s.copy(null,t,null,null,null):s=s.copy(null,null,null,null,s.right.insert(e,t,i)),s.fixUp_()}removeMin_(){if(this.left.isEmpty())return H.EMPTY_NODE;let e=this;return!e.left.isRed_()&&!e.left.left.isRed_()&&(e=e.moveRedLeft_()),e=e.copy(null,null,null,e.left.removeMin_(),null),e.fixUp_()}remove(e,t){let i,s;if(i=this,t(e,i.key)<0)!i.left.isEmpty()&&!i.left.isRed_()&&!i.left.left.isRed_()&&(i=i.moveRedLeft_()),i=i.copy(null,null,null,i.left.remove(e,t),null);else{if(i.left.isRed_()&&(i=i.rotateRight_()),!i.right.isEmpty()&&!i.right.isRed_()&&!i.right.left.isRed_()&&(i=i.moveRedRight_()),t(e,i.key)===0){if(i.right.isEmpty())return H.EMPTY_NODE;s=i.right.min_(),i=i.copy(s.key,s.value,null,null,i.right.removeMin_())}i=i.copy(null,null,null,null,i.right.remove(e,t))}return i.fixUp_()}isRed_(){return this.color}fixUp_(){let e=this;return e.right.isRed_()&&!e.left.isRed_()&&(e=e.rotateLeft_()),e.left.isRed_()&&e.left.left.isRed_()&&(e=e.rotateRight_()),e.left.isRed_()&&e.right.isRed_()&&(e=e.colorFlip_()),e}moveRedLeft_(){let e=this.colorFlip_();return e.right.left.isRed_()&&(e=e.copy(null,null,null,null,e.right.rotateRight_()),e=e.rotateLeft_(),e=e.colorFlip_()),e}moveRedRight_(){let e=this.colorFlip_();return e.left.left.isRed_()&&(e=e.rotateRight_(),e=e.colorFlip_()),e}rotateLeft_(){const e=this.copy(null,null,M.RED,null,this.right.left);return this.right.copy(null,null,this.color,e,null)}rotateRight_(){const e=this.copy(null,null,M.RED,this.left.right,null);return this.left.copy(null,null,this.color,null,e)}colorFlip_(){const e=this.left.copy(null,null,!this.left.color,null,null),t=this.right.copy(null,null,!this.right.color,null,null);return this.copy(null,null,!this.color,e,t)}checkMaxDepth_(){const e=this.check_();return Math.pow(2,e)<=this.count()+1}check_(){if(this.isRed_()&&this.left.isRed_())throw new Error("Red node has red child("+this.key+","+this.value+")");if(this.right.isRed_())throw new Error("Right child of ("+this.key+","+this.value+") is red");const e=this.left.check_();if(e!==this.right.check_())throw new Error("Black depths differ");return e+(this.isRed_()?0:1)}}M.RED=!0,M.BLACK=!1;class Ac{copy(e,t,i,s,r){return this}insert(e,t,i){return new M(e,t,null)}remove(e,t){return this}count(){return 0}isEmpty(){return!0}inorderTraversal(e){return!1}reverseTraversal(e){return!1}minKey(){return null}maxKey(){return null}check_(){return 0}isRed_(){return!1}}class H{constructor(e,t=H.EMPTY_NODE){this.comparator_=e,this.root_=t}insert(e,t){return new H(this.comparator_,this.root_.insert(e,t,this.comparator_).copy(null,null,M.BLACK,null,null))}remove(e){return new H(this.comparator_,this.root_.remove(e,this.comparator_).copy(null,null,M.BLACK,null,null))}get(e){let t,i=this.root_;for(;!i.isEmpty();){if(t=this.comparator_(e,i.key),t===0)return i.value;t<0?i=i.left:t>0&&(i=i.right)}return null}getPredecessorKey(e){let t,i=this.root_,s=null;for(;!i.isEmpty();)if(t=this.comparator_(e,i.key),t===0){if(i.left.isEmpty())return s?s.key:null;for(i=i.left;!i.right.isEmpty();)i=i.right;return i.key}else t<0?i=i.left:t>0&&(s=i,i=i.right);throw new Error("Attempted to find predecessor key for a nonexistent key.  What gives?")}isEmpty(){return this.root_.isEmpty()}count(){return this.root_.count()}minKey(){return this.root_.minKey()}maxKey(){return this.root_.maxKey()}inorderTraversal(e){return this.root_.inorderTraversal(e)}reverseTraversal(e){return this.root_.reverseTraversal(e)}getIterator(e){return new $t(this.root_,null,this.comparator_,!1,e)}getIteratorFrom(e,t){return new $t(this.root_,e,this.comparator_,!1,t)}getReverseIteratorFrom(e,t){return new $t(this.root_,e,this.comparator_,!0,t)}getReverseIterator(e){return new $t(this.root_,null,this.comparator_,!0,e)}}H.EMPTY_NODE=new Ac;/**
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
 */function kc(n,e){return Re(n.name,e.name)}function Jn(n,e){return Re(n,e)}/**
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
 */let Xn;function Nc(n){Xn=n}const mr=function(n){return typeof n=="number"?"number:"+Ls(n):"string:"+n},gr=function(n){if(n.isLeafNode()){const e=n.val();f(typeof e=="string"||typeof e=="number"||typeof e=="object"&&J(e,".sv"),"Priority must be a string or number.")}else f(n===Xn||n.isEmpty(),"priority of unexpected type.");f(n===Xn||n.getPriority().isEmpty(),"Priority nodes can't have a priority of their own.")};/**
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
 */let yr;class L{static set __childrenNodeConstructor(e){yr=e}static get __childrenNodeConstructor(){return yr}constructor(e,t=L.__childrenNodeConstructor.EMPTY_NODE){this.value_=e,this.priorityNode_=t,this.lazyHash_=null,f(this.value_!==void 0&&this.value_!==null,"LeafNode shouldn't be created with null/undefined value."),gr(this.priorityNode_)}isLeafNode(){return!0}getPriority(){return this.priorityNode_}updatePriority(e){return new L(this.value_,e)}getImmediateChild(e){return e===".priority"?this.priorityNode_:L.__childrenNodeConstructor.EMPTY_NODE}getChild(e){return v(e)?this:y(e)===".priority"?this.priorityNode_:L.__childrenNodeConstructor.EMPTY_NODE}hasChild(){return!1}getPredecessorChildName(e,t){return null}updateImmediateChild(e,t){return e===".priority"?this.updatePriority(t):t.isEmpty()&&e!==".priority"?this:L.__childrenNodeConstructor.EMPTY_NODE.updateImmediateChild(e,t).updatePriority(this.priorityNode_)}updateChild(e,t){const i=y(e);return i===null?t:t.isEmpty()&&i!==".priority"?this:(f(i!==".priority"||me(e)===1,".priority must be the last token in a path"),this.updateImmediateChild(i,L.__childrenNodeConstructor.EMPTY_NODE.updateChild(x(e),t)))}isEmpty(){return!1}numChildren(){return 0}forEachChild(e,t){return!1}val(e){return e&&!this.getPriority().isEmpty()?{".value":this.getValue(),".priority":this.getPriority().val()}:this.getValue()}hash(){if(this.lazyHash_===null){let e="";this.priorityNode_.isEmpty()||(e+="priority:"+mr(this.priorityNode_.val())+":");const t=typeof this.value_;e+=t+":",t==="number"?e+=Ls(this.value_):e+=this.value_,this.lazyHash_=Ps(e)}return this.lazyHash_}getValue(){return this.value_}compareTo(e){return e===L.__childrenNodeConstructor.EMPTY_NODE?1:e instanceof L.__childrenNodeConstructor?-1:(f(e.isLeafNode(),"Unknown node type"),this.compareToLeafNode_(e))}compareToLeafNode_(e){const t=typeof e.value_,i=typeof this.value_,s=L.VALUE_TYPE_ORDER.indexOf(t),r=L.VALUE_TYPE_ORDER.indexOf(i);return f(s>=0,"Unknown leaf type: "+t),f(r>=0,"Unknown leaf type: "+i),s===r?i==="object"?0:this.value_<e.value_?-1:this.value_===e.value_?0:1:r-s}withIndex(){return this}isIndexed(){return!0}equals(e){if(e===this)return!0;if(e.isLeafNode()){const t=e;return this.value_===t.value_&&this.priorityNode_.equals(t.priorityNode_)}else return!1}}L.VALUE_TYPE_ORDER=["object","boolean","number","string"];/**
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
 */let vr,br;function Rc(n){vr=n}function Pc(n){br=n}class Dc extends Wt{compare(e,t){const i=e.node.getPriority(),s=t.node.getPriority(),r=i.compareTo(s);return r===0?Re(e.name,t.name):r}isDefinedOn(e){return!e.getPriority().isEmpty()}indexedValueChanged(e,t){return!e.getPriority().equals(t.getPriority())}minPost(){return b.MIN}maxPost(){return new b(ce,new L("[PRIORITY-POST]",br))}makePost(e,t){const i=vr(e);return new b(t,new L("[PRIORITY-POST]",i))}toString(){return".priority"}}const T=new Dc;/**
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
 */const Oc=Math.log(2);class Mc{constructor(e){const t=r=>parseInt(Math.log(r)/Oc,10),i=r=>parseInt(Array(r+1).join("1"),2);this.count=t(e+1),this.current_=this.count-1;const s=i(this.count);this.bits_=e+1&s}nextBitIsOne(){const e=!(this.bits_&1<<this.current_);return this.current_--,e}}const Ht=function(n,e,t,i){n.sort(e);const s=function(l,c){const d=c-l;let h,u;if(d===0)return null;if(d===1)return h=n[l],u=t?t(h):h,new M(u,h.node,M.BLACK,null,null);{const p=parseInt(d/2,10)+l,_=s(l,p),C=s(p+1,c);return h=n[p],u=t?t(h):h,new M(u,h.node,M.BLACK,_,C)}},r=function(l){let c=null,d=null,h=n.length;const u=function(_,C){const F=h-_,et=h;h-=_;const yn=s(F+1,et),ji=n[F],ku=t?t(ji):ji;p(new M(ku,ji.node,C,null,yn))},p=function(_){c?(c.left=_,c=_):(d=_,c=_)};for(let _=0;_<l.count;++_){const C=l.nextBitIsOne(),F=Math.pow(2,l.count-(_+1));C?u(F,M.BLACK):(u(F,M.BLACK),u(F,M.RED))}return d},o=new Mc(n.length),a=r(o);return new H(i||e,a)};/**
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
 */let Zn;const Ge={};class he{static get Default(){return f(Ge&&T,"ChildrenNode.ts has not been loaded"),Zn=Zn||new he({".priority":Ge},{".priority":T}),Zn}constructor(e,t){this.indexes_=e,this.indexSet_=t}get(e){const t=Ie(this.indexes_,e);if(!t)throw new Error("No index defined for "+e);return t instanceof H?t:null}hasIndex(e){return J(this.indexSet_,e.toString())}addIndex(e,t){f(e!==ne,"KeyIndex always exists and isn't meant to be added to the IndexMap.");const i=[];let s=!1;const r=t.getIterator(b.Wrap);let o=r.getNext();for(;o;)s=s||e.isDefinedOn(o.node),i.push(o),o=r.getNext();let a;s?a=Ht(i,e.getCompare()):a=Ge;const l=e.toString(),c={...this.indexSet_};c[l]=e;const d={...this.indexes_};return d[l]=a,new he(d,c)}addToIndexes(e,t){const i=Pt(this.indexes_,(s,r)=>{const o=Ie(this.indexSet_,r);if(f(o,"Missing index implementation for "+r),s===Ge)if(o.isDefinedOn(e.node)){const a=[],l=t.getIterator(b.Wrap);let c=l.getNext();for(;c;)c.name!==e.name&&a.push(c),c=l.getNext();return a.push(e),Ht(a,o.getCompare())}else return Ge;else{const a=t.get(e.name);let l=s;return a&&(l=l.remove(new b(e.name,a))),l.insert(e,e.node)}});return new he(i,this.indexSet_)}removeFromIndexes(e,t){const i=Pt(this.indexes_,s=>{if(s===Ge)return s;{const r=t.get(e.name);return r?s.remove(new b(e.name,r)):s}});return new he(i,this.indexSet_)}}/**
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
 */let dt;class m{static get EMPTY_NODE(){return dt||(dt=new m(new H(Jn),null,he.Default))}constructor(e,t,i){this.children_=e,this.priorityNode_=t,this.indexMap_=i,this.lazyHash_=null,this.priorityNode_&&gr(this.priorityNode_),this.children_.isEmpty()&&f(!this.priorityNode_||this.priorityNode_.isEmpty(),"An empty node cannot have a priority")}isLeafNode(){return!1}getPriority(){return this.priorityNode_||dt}updatePriority(e){return this.children_.isEmpty()?this:new m(this.children_,e,this.indexMap_)}getImmediateChild(e){if(e===".priority")return this.getPriority();{const t=this.children_.get(e);return t===null?dt:t}}getChild(e){const t=y(e);return t===null?this:this.getImmediateChild(t).getChild(x(e))}hasChild(e){return this.children_.get(e)!==null}updateImmediateChild(e,t){if(f(t,"We should always be passing snapshot nodes"),e===".priority")return this.updatePriority(t);{const i=new b(e,t);let s,r;t.isEmpty()?(s=this.children_.remove(e),r=this.indexMap_.removeFromIndexes(i,this.children_)):(s=this.children_.insert(e,t),r=this.indexMap_.addToIndexes(i,this.children_));const o=s.isEmpty()?dt:this.priorityNode_;return new m(s,o,r)}}updateChild(e,t){const i=y(e);if(i===null)return t;{f(y(e)!==".priority"||me(e)===1,".priority must be the last token in a path");const s=this.getImmediateChild(i).updateChild(x(e),t);return this.updateImmediateChild(i,s)}}isEmpty(){return this.children_.isEmpty()}numChildren(){return this.children_.count()}val(e){if(this.isEmpty())return null;const t={};let i=0,s=0,r=!0;if(this.forEachChild(T,(o,a)=>{t[o]=a.val(e),i++,r&&m.INTEGER_REGEXP_.test(o)?s=Math.max(s,Number(o)):r=!1}),!e&&r&&s<2*i){const o=[];for(const a in t)o[a]=t[a];return o}else return e&&!this.getPriority().isEmpty()&&(t[".priority"]=this.getPriority().val()),t}hash(){if(this.lazyHash_===null){let e="";this.getPriority().isEmpty()||(e+="priority:"+mr(this.getPriority().val())+":"),this.forEachChild(T,(t,i)=>{const s=i.hash();s!==""&&(e+=":"+t+":"+s)}),this.lazyHash_=e===""?"":Ps(e)}return this.lazyHash_}getPredecessorChildName(e,t,i){const s=this.resolveIndex_(i);if(s){const r=s.getPredecessorKey(new b(e,t));return r?r.name:null}else return this.children_.getPredecessorKey(e)}getFirstChildName(e){const t=this.resolveIndex_(e);if(t){const i=t.minKey();return i&&i.name}else return this.children_.minKey()}getFirstChild(e){const t=this.getFirstChildName(e);return t?new b(t,this.children_.get(t)):null}getLastChildName(e){const t=this.resolveIndex_(e);if(t){const i=t.maxKey();return i&&i.name}else return this.children_.maxKey()}getLastChild(e){const t=this.getLastChildName(e);return t?new b(t,this.children_.get(t)):null}forEachChild(e,t){const i=this.resolveIndex_(e);return i?i.inorderTraversal(s=>t(s.name,s.node)):this.children_.inorderTraversal(t)}getIterator(e){return this.getIteratorFrom(e.minPost(),e)}getIteratorFrom(e,t){const i=this.resolveIndex_(t);if(i)return i.getIteratorFrom(e,s=>s);{const s=this.children_.getIteratorFrom(e.name,b.Wrap);let r=s.peek();for(;r!=null&&t.compare(r,e)<0;)s.getNext(),r=s.peek();return s}}getReverseIterator(e){return this.getReverseIteratorFrom(e.maxPost(),e)}getReverseIteratorFrom(e,t){const i=this.resolveIndex_(t);if(i)return i.getReverseIteratorFrom(e,s=>s);{const s=this.children_.getReverseIteratorFrom(e.name,b.Wrap);let r=s.peek();for(;r!=null&&t.compare(r,e)>0;)s.getNext(),r=s.peek();return s}}compareTo(e){return this.isEmpty()?e.isEmpty()?0:-1:e.isLeafNode()||e.isEmpty()?1:e===ut?-1:0}withIndex(e){if(e===ne||this.indexMap_.hasIndex(e))return this;{const t=this.indexMap_.addIndex(e,this.children_);return new m(this.children_,this.priorityNode_,t)}}isIndexed(e){return e===ne||this.indexMap_.hasIndex(e)}equals(e){if(e===this)return!0;if(e.isLeafNode())return!1;{const t=e;if(this.getPriority().equals(t.getPriority()))if(this.children_.count()===t.children_.count()){const i=this.getIterator(T),s=t.getIterator(T);let r=i.getNext(),o=s.getNext();for(;r&&o;){if(r.name!==o.name||!r.node.equals(o.node))return!1;r=i.getNext(),o=s.getNext()}return r===null&&o===null}else return!1;else return!1}}resolveIndex_(e){return e===ne?null:this.indexMap_.get(e.toString())}}m.INTEGER_REGEXP_=/^(0|[1-9]\d*)$/;class Lc extends m{constructor(){super(new H(Jn),m.EMPTY_NODE,he.Default)}compareTo(e){return e===this?0:1}equals(e){return e===this}getPriority(){return this}getImmediateChild(e){return m.EMPTY_NODE}isEmpty(){return!1}}const ut=new Lc;Object.defineProperties(b,{MIN:{value:new b(pe,m.EMPTY_NODE)},MAX:{value:new b(ce,ut)}}),_r.__EMPTY_NODE=m.EMPTY_NODE,L.__childrenNodeConstructor=m,Nc(ut),Pc(ut);/**
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
 */const Fc=!0;function N(n,e=null){if(n===null)return m.EMPTY_NODE;if(typeof n=="object"&&".priority"in n&&(e=n[".priority"]),f(e===null||typeof e=="string"||typeof e=="number"||typeof e=="object"&&".sv"in e,"Invalid priority type found: "+typeof e),typeof n=="object"&&".value"in n&&n[".value"]!==null&&(n=n[".value"]),typeof n!="object"||".sv"in n){const t=n;return new L(t,N(e))}if(!(n instanceof Array)&&Fc){const t=[];let i=!1;if(O(n,(o,a)=>{if(o.substring(0,1)!=="."){const l=N(a);l.isEmpty()||(i=i||!l.getPriority().isEmpty(),t.push(new b(o,l)))}}),t.length===0)return m.EMPTY_NODE;const r=Ht(t,kc,o=>o.name,Jn);if(i){const o=Ht(t,T.getCompare());return new m(r,N(e),new he({".priority":o},{".priority":T}))}else return new m(r,N(e),he.Default)}else{let t=m.EMPTY_NODE;return O(n,(i,s)=>{if(J(n,i)&&i.substring(0,1)!=="."){const r=N(s);(r.isLeafNode()||!r.isEmpty())&&(t=t.updateImmediateChild(i,r))}}),t.updatePriority(N(e))}}Rc(N);/**
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
 */class ei extends Wt{constructor(e){super(),this.indexPath_=e,f(!v(e)&&y(e)!==".priority","Can't create PathIndex with empty path or .priority key")}extractChild(e){return e.getChild(this.indexPath_)}isDefinedOn(e){return!e.getChild(this.indexPath_).isEmpty()}compare(e,t){const i=this.extractChild(e.node),s=this.extractChild(t.node),r=i.compareTo(s);return r===0?Re(e.name,t.name):r}makePost(e,t){const i=N(e),s=m.EMPTY_NODE.updateChild(this.indexPath_,i);return new b(t,s)}maxPost(){const e=m.EMPTY_NODE.updateChild(this.indexPath_,ut);return new b(ce,e)}toString(){return ct(this.indexPath_,0).join("/")}}/**
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
 */class Bc extends Wt{compare(e,t){const i=e.node.compareTo(t.node);return i===0?Re(e.name,t.name):i}isDefinedOn(e){return!0}indexedValueChanged(e,t){return!e.equals(t)}minPost(){return b.MIN}maxPost(){return b.MAX}makePost(e,t){const i=N(e);return new b(t,i)}toString(){return".value"}}const ti=new Bc;/**
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
 */function wr(n){return{type:"value",snapshotNode:n}}function qe(n,e){return{type:"child_added",snapshotNode:e,childName:n}}function ft(n,e){return{type:"child_removed",snapshotNode:e,childName:n}}function pt(n,e,t){return{type:"child_changed",snapshotNode:e,childName:n,oldSnap:t}}function Wc(n,e){return{type:"child_moved",snapshotNode:e,childName:n}}/**
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
 */class ni{constructor(e){this.index_=e}updateChild(e,t,i,s,r,o){f(e.isIndexed(this.index_),"A node must be indexed if only a child is updated");const a=e.getImmediateChild(t);return a.getChild(s).equals(i.getChild(s))&&a.isEmpty()===i.isEmpty()||(o!=null&&(i.isEmpty()?e.hasChild(t)?o.trackChildChange(ft(t,a)):f(e.isLeafNode(),"A child remove without an old child only makes sense on a leaf node"):a.isEmpty()?o.trackChildChange(qe(t,i)):o.trackChildChange(pt(t,i,a))),e.isLeafNode()&&i.isEmpty())?e:e.updateImmediateChild(t,i).withIndex(this.index_)}updateFullNode(e,t,i){return i!=null&&(e.isLeafNode()||e.forEachChild(T,(s,r)=>{t.hasChild(s)||i.trackChildChange(ft(s,r))}),t.isLeafNode()||t.forEachChild(T,(s,r)=>{if(e.hasChild(s)){const o=e.getImmediateChild(s);o.equals(r)||i.trackChildChange(pt(s,r,o))}else i.trackChildChange(qe(s,r))})),t.withIndex(this.index_)}updatePriority(e,t){return e.isEmpty()?m.EMPTY_NODE:e.updatePriority(t)}filtersNodes(){return!1}getIndexedFilter(){return this}getIndex(){return this.index_}}/**
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
 */class _t{constructor(e){this.indexedFilter_=new ni(e.getIndex()),this.index_=e.getIndex(),this.startPost_=_t.getStartPost_(e),this.endPost_=_t.getEndPost_(e),this.startIsInclusive_=!e.startAfterSet_,this.endIsInclusive_=!e.endBeforeSet_}getStartPost(){return this.startPost_}getEndPost(){return this.endPost_}matches(e){const t=this.startIsInclusive_?this.index_.compare(this.getStartPost(),e)<=0:this.index_.compare(this.getStartPost(),e)<0,i=this.endIsInclusive_?this.index_.compare(e,this.getEndPost())<=0:this.index_.compare(e,this.getEndPost())<0;return t&&i}updateChild(e,t,i,s,r,o){return this.matches(new b(t,i))||(i=m.EMPTY_NODE),this.indexedFilter_.updateChild(e,t,i,s,r,o)}updateFullNode(e,t,i){t.isLeafNode()&&(t=m.EMPTY_NODE);let s=t.withIndex(this.index_);s=s.updatePriority(m.EMPTY_NODE);const r=this;return t.forEachChild(T,(o,a)=>{r.matches(new b(o,a))||(s=s.updateImmediateChild(o,m.EMPTY_NODE))}),this.indexedFilter_.updateFullNode(e,s,i)}updatePriority(e,t){return e}filtersNodes(){return!0}getIndexedFilter(){return this.indexedFilter_}getIndex(){return this.index_}static getStartPost_(e){if(e.hasStart()){const t=e.getIndexStartName();return e.getIndex().makePost(e.getIndexStartValue(),t)}else return e.getIndex().minPost()}static getEndPost_(e){if(e.hasEnd()){const t=e.getIndexEndName();return e.getIndex().makePost(e.getIndexEndValue(),t)}else return e.getIndex().maxPost()}}/**
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
 */class Uc{constructor(e){this.withinDirectionalStart=t=>this.reverse_?this.withinEndPost(t):this.withinStartPost(t),this.withinDirectionalEnd=t=>this.reverse_?this.withinStartPost(t):this.withinEndPost(t),this.withinStartPost=t=>{const i=this.index_.compare(this.rangedFilter_.getStartPost(),t);return this.startIsInclusive_?i<=0:i<0},this.withinEndPost=t=>{const i=this.index_.compare(t,this.rangedFilter_.getEndPost());return this.endIsInclusive_?i<=0:i<0},this.rangedFilter_=new _t(e),this.index_=e.getIndex(),this.limit_=e.getLimit(),this.reverse_=!e.isViewFromLeft(),this.startIsInclusive_=!e.startAfterSet_,this.endIsInclusive_=!e.endBeforeSet_}updateChild(e,t,i,s,r,o){return this.rangedFilter_.matches(new b(t,i))||(i=m.EMPTY_NODE),e.getImmediateChild(t).equals(i)?e:e.numChildren()<this.limit_?this.rangedFilter_.getIndexedFilter().updateChild(e,t,i,s,r,o):this.fullLimitUpdateChild_(e,t,i,r,o)}updateFullNode(e,t,i){let s;if(t.isLeafNode()||t.isEmpty())s=m.EMPTY_NODE.withIndex(this.index_);else if(this.limit_*2<t.numChildren()&&t.isIndexed(this.index_)){s=m.EMPTY_NODE.withIndex(this.index_);let r;this.reverse_?r=t.getReverseIteratorFrom(this.rangedFilter_.getEndPost(),this.index_):r=t.getIteratorFrom(this.rangedFilter_.getStartPost(),this.index_);let o=0;for(;r.hasNext()&&o<this.limit_;){const a=r.getNext();if(this.withinDirectionalStart(a))if(this.withinDirectionalEnd(a))s=s.updateImmediateChild(a.name,a.node),o++;else break;else continue}}else{s=t.withIndex(this.index_),s=s.updatePriority(m.EMPTY_NODE);let r;this.reverse_?r=s.getReverseIterator(this.index_):r=s.getIterator(this.index_);let o=0;for(;r.hasNext();){const a=r.getNext();o<this.limit_&&this.withinDirectionalStart(a)&&this.withinDirectionalEnd(a)?o++:s=s.updateImmediateChild(a.name,m.EMPTY_NODE)}}return this.rangedFilter_.getIndexedFilter().updateFullNode(e,s,i)}updatePriority(e,t){return e}filtersNodes(){return!0}getIndexedFilter(){return this.rangedFilter_.getIndexedFilter()}getIndex(){return this.index_}fullLimitUpdateChild_(e,t,i,s,r){let o;if(this.reverse_){const h=this.index_.getCompare();o=(u,p)=>h(p,u)}else o=this.index_.getCompare();const a=e;f(a.numChildren()===this.limit_,"");const l=new b(t,i),c=this.reverse_?a.getFirstChild(this.index_):a.getLastChild(this.index_),d=this.rangedFilter_.matches(l);if(a.hasChild(t)){const h=a.getImmediateChild(t);let u=s.getChildAfterChild(this.index_,c,this.reverse_);for(;u!=null&&(u.name===t||a.hasChild(u.name));)u=s.getChildAfterChild(this.index_,u,this.reverse_);const p=u==null?1:o(u,l);if(d&&!i.isEmpty()&&p>=0)return r!=null&&r.trackChildChange(pt(t,i,h)),a.updateImmediateChild(t,i);{r!=null&&r.trackChildChange(ft(t,h));const C=a.updateImmediateChild(t,m.EMPTY_NODE);return u!=null&&this.rangedFilter_.matches(u)?(r!=null&&r.trackChildChange(qe(u.name,u.node)),C.updateImmediateChild(u.name,u.node)):C}}else return i.isEmpty()?e:d&&o(c,l)>=0?(r!=null&&(r.trackChildChange(ft(c.name,c.node)),r.trackChildChange(qe(t,i))),a.updateImmediateChild(t,i).updateImmediateChild(c.name,m.EMPTY_NODE)):e}}/**
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
 */class Vt{constructor(){this.limitSet_=!1,this.startSet_=!1,this.startNameSet_=!1,this.startAfterSet_=!1,this.endSet_=!1,this.endNameSet_=!1,this.endBeforeSet_=!1,this.limit_=0,this.viewFrom_="",this.indexStartValue_=null,this.indexStartName_="",this.indexEndValue_=null,this.indexEndName_="",this.index_=T}hasStart(){return this.startSet_}isViewFromLeft(){return this.viewFrom_===""?this.startSet_:this.viewFrom_==="l"}getIndexStartValue(){return f(this.startSet_,"Only valid if start has been set"),this.indexStartValue_}getIndexStartName(){return f(this.startSet_,"Only valid if start has been set"),this.startNameSet_?this.indexStartName_:pe}hasEnd(){return this.endSet_}getIndexEndValue(){return f(this.endSet_,"Only valid if end has been set"),this.indexEndValue_}getIndexEndName(){return f(this.endSet_,"Only valid if end has been set"),this.endNameSet_?this.indexEndName_:ce}hasLimit(){return this.limitSet_}hasAnchoredLimit(){return this.limitSet_&&this.viewFrom_!==""}getLimit(){return f(this.limitSet_,"Only valid if limit has been set"),this.limit_}getIndex(){return this.index_}loadsAllData(){return!(this.startSet_||this.endSet_||this.limitSet_)}isDefault(){return this.loadsAllData()&&this.index_===T}copy(){const e=new Vt;return e.limitSet_=this.limitSet_,e.limit_=this.limit_,e.startSet_=this.startSet_,e.startAfterSet_=this.startAfterSet_,e.indexStartValue_=this.indexStartValue_,e.startNameSet_=this.startNameSet_,e.indexStartName_=this.indexStartName_,e.endSet_=this.endSet_,e.endBeforeSet_=this.endBeforeSet_,e.indexEndValue_=this.indexEndValue_,e.endNameSet_=this.endNameSet_,e.indexEndName_=this.indexEndName_,e.index_=this.index_,e.viewFrom_=this.viewFrom_,e}}function $c(n){return n.loadsAllData()?new ni(n.getIndex()):n.hasLimit()?new Uc(n):new _t(n)}function Hc(n,e){const t=n.copy();return t.limitSet_=!0,t.limit_=e,t.viewFrom_="l",t}function Vc(n,e){const t=n.copy();return t.limitSet_=!0,t.limit_=e,t.viewFrom_="r",t}function ii(n,e,t){const i=n.copy();return i.startSet_=!0,e===void 0&&(e=null),i.indexStartValue_=e,t!=null?(i.startNameSet_=!0,i.indexStartName_=t):(i.startNameSet_=!1,i.indexStartName_=""),i}function zc(n,e,t){let i;return n.index_===ne||t?i=ii(n,e,t):i=ii(n,e,ce),i.startAfterSet_=!0,i}function si(n,e,t){const i=n.copy();return i.endSet_=!0,e===void 0&&(e=null),i.indexEndValue_=e,t!==void 0?(i.endNameSet_=!0,i.indexEndName_=t):(i.endNameSet_=!1,i.indexEndName_=""),i}function jc(n,e,t){let i;return n.index_===ne||t?i=si(n,e,t):i=si(n,e,pe),i.endBeforeSet_=!0,i}function zt(n,e){const t=n.copy();return t.index_=e,t}function Cr(n){const e={};if(n.isDefault())return e;let t;if(n.index_===T?t="$priority":n.index_===ti?t="$value":n.index_===ne?t="$key":(f(n.index_ instanceof ei,"Unrecognized index type!"),t=n.index_.toString()),e.orderBy=P(t),n.startSet_){const i=n.startAfterSet_?"startAfter":"startAt";e[i]=P(n.indexStartValue_),n.startNameSet_&&(e[i]+=","+P(n.indexStartName_))}if(n.endSet_){const i=n.endBeforeSet_?"endBefore":"endAt";e[i]=P(n.indexEndValue_),n.endNameSet_&&(e[i]+=","+P(n.indexEndName_))}return n.limitSet_&&(n.isViewFromLeft()?e.limitToFirst=n.limit_:e.limitToLast=n.limit_),e}function Er(n){const e={};if(n.startSet_&&(e.sp=n.indexStartValue_,n.startNameSet_&&(e.sn=n.indexStartName_),e.sin=!n.startAfterSet_),n.endSet_&&(e.ep=n.indexEndValue_,n.endNameSet_&&(e.en=n.indexEndName_),e.ein=!n.endBeforeSet_),n.limitSet_){e.l=n.limit_;let t=n.viewFrom_;t===""&&(n.isViewFromLeft()?t="l":t="r"),e.vf=t}return n.index_!==T&&(e.i=n.index_.toString()),e}/**
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
 */class jt extends ar{reportStats(e){throw new Error("Method not implemented.")}static getListenId_(e,t){return t!==void 0?"tag$"+t:(f(e._queryParams.isDefault(),"should have a tag if it's not a default query."),e._path.toString())}constructor(e,t,i,s){super(),this.repoInfo_=e,this.onDataUpdate_=t,this.authTokenProvider_=i,this.appCheckTokenProvider_=s,this.log_=ot("p:rest:"),this.listens_={}}listen(e,t,i,s){const r=e._path.toString();this.log_("Listen called for "+r+" "+e._queryIdentifier);const o=jt.getListenId_(e,i),a={};this.listens_[o]=a;const l=Cr(e._queryParams);this.restRequest_(r+".json",l,(c,d)=>{let h=d;if(c===404&&(h=null,c=null),c===null&&this.onDataUpdate_(r,h,!1,i),Ie(this.listens_,o)===a){let u;c?c===401?u="permission_denied":u="rest_error:"+c:u="ok",s(u,null)}})}unlisten(e,t){const i=jt.getListenId_(e,t);delete this.listens_[i]}get(e){const t=Cr(e._queryParams),i=e._path.toString(),s=new z;return this.restRequest_(i+".json",t,(r,o)=>{let a=o;r===404&&(a=null,r=null),r===null?(this.onDataUpdate_(i,a,!1,null),s.resolve(a)):s.reject(new Error(a))}),s.promise}refreshAuthToken(e){}restRequest_(e,t={},i){return t.format="export",Promise.all([this.authTokenProvider_.getToken(!1),this.appCheckTokenProvider_.getToken(!1)]).then(([s,r])=>{s&&s.accessToken&&(t.auth=s.accessToken),r&&r.token&&(t.ac=r.token);const o=(this.repoInfo_.secure?"https://":"http://")+this.repoInfo_.host+e+"?ns="+this.repoInfo_.namespace+pa(t);this.log_("Sending REST request for "+o);const a=new XMLHttpRequest;a.onreadystatechange=()=>{if(i&&a.readyState===4){this.log_("REST Response for "+o+" received. status:",a.status,"response:",a.responseText);let l=null;if(a.status>=200&&a.status<300){try{l=tt(a.responseText)}catch{B("Failed to parse JSON response for "+o+": "+a.responseText)}i(null,l)}else a.status!==401&&a.status!==404&&B("Got unsuccessful REST response for "+o+" Status: "+a.status),i(a.status);i=null}},a.open("GET",o,!0),a.send()})}}/**
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
 */class Gc{constructor(){this.rootNode_=m.EMPTY_NODE}getNode(e){return this.rootNode_.getChild(e)}updateSnapshot(e,t){this.rootNode_=this.rootNode_.updateChild(e,t)}}/**
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
 */function Gt(){return{value:null,children:new Map}}function Ke(n,e,t){if(v(e))n.value=t,n.children.clear();else if(n.value!==null)n.value=n.value.updateChild(e,t);else{const i=y(e);n.children.has(i)||n.children.set(i,Gt());const s=n.children.get(i);e=x(e),Ke(s,e,t)}}function ri(n,e){if(v(e))return n.value=null,n.children.clear(),!0;if(n.value!==null){if(n.value.isLeafNode())return!1;{const t=n.value;return n.value=null,t.forEachChild(T,(i,s)=>{Ke(n,new E(i),s)}),ri(n,e)}}else if(n.children.size>0){const t=y(e);return e=x(e),n.children.has(t)&&ri(n.children.get(t),e)&&n.children.delete(t),n.children.size===0}else return!0}function oi(n,e,t){n.value!==null?t(e,n.value):qc(n,(i,s)=>{const r=new E(e.toString()+"/"+i);oi(s,r,t)})}function qc(n,e){n.children.forEach((t,i)=>{e(i,t)})}/**
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
 */class Kc{constructor(e){this.collection_=e,this.last_=null}get(){const e=this.collection_.get(),t={...e};return this.last_&&O(this.last_,(i,s)=>{t[i]=t[i]-s}),this.last_=e,t}}/**
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
 */const Sr=10*1e3,Qc=30*1e3,Yc=300*1e3;class Jc{constructor(e,t){this.server_=t,this.statsToReport_={},this.statsListener_=new Kc(e);const i=Sr+(Qc-Sr)*Math.random();lt(this.reportStats_.bind(this),Math.floor(i))}reportStats_(){const e=this.statsListener_.get(),t={};let i=!1;O(e,(s,r)=>{r>0&&J(this.statsToReport_,s)&&(t[s]=r,i=!0)}),i&&this.server_.reportStats(t),lt(this.reportStats_.bind(this),Math.floor(Math.random()*2*Yc))}}/**
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
 */var X;(function(n){n[n.OVERWRITE=0]="OVERWRITE",n[n.MERGE=1]="MERGE",n[n.ACK_USER_WRITE=2]="ACK_USER_WRITE",n[n.LISTEN_COMPLETE=3]="LISTEN_COMPLETE"})(X||(X={}));function ai(){return{fromUser:!0,fromServer:!1,queryId:null,tagged:!1}}function li(){return{fromUser:!1,fromServer:!0,queryId:null,tagged:!1}}function ci(n){return{fromUser:!1,fromServer:!0,queryId:n,tagged:!0}}/**
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
 */class qt{constructor(e,t,i){this.path=e,this.affectedTree=t,this.revert=i,this.type=X.ACK_USER_WRITE,this.source=ai()}operationForChild(e){if(v(this.path)){if(this.affectedTree.value!=null)return f(this.affectedTree.children.isEmpty(),"affectedTree should not have overlapping affected paths."),this;{const t=this.affectedTree.subtree(new E(e));return new qt(w(),t,this.revert)}}else return f(y(this.path)===e,"operationForChild called for unrelated child."),new qt(x(this.path),this.affectedTree,this.revert)}}/**
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
 */class mt{constructor(e,t){this.source=e,this.path=t,this.type=X.LISTEN_COMPLETE}operationForChild(e){return v(this.path)?new mt(this.source,w()):new mt(this.source,x(this.path))}}/**
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
 */class De{constructor(e,t,i){this.source=e,this.path=t,this.snap=i,this.type=X.OVERWRITE}operationForChild(e){return v(this.path)?new De(this.source,w(),this.snap.getImmediateChild(e)):new De(this.source,x(this.path),this.snap)}}/**
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
 */class Qe{constructor(e,t,i){this.source=e,this.path=t,this.children=i,this.type=X.MERGE}operationForChild(e){if(v(this.path)){const t=this.children.subtree(new E(e));return t.isEmpty()?null:t.value?new De(this.source,w(),t.value):new Qe(this.source,w(),t)}else return f(y(this.path)===e,"Can't get a merge for a child not on the path of the operation"),new Qe(this.source,x(this.path),this.children)}toString(){return"Operation("+this.path+": "+this.source.toString()+" merge: "+this.children.toString()+")"}}/**
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
 */class ge{constructor(e,t,i){this.node_=e,this.fullyInitialized_=t,this.filtered_=i}isFullyInitialized(){return this.fullyInitialized_}isFiltered(){return this.filtered_}isCompleteForPath(e){if(v(e))return this.isFullyInitialized()&&!this.filtered_;const t=y(e);return this.isCompleteForChild(t)}isCompleteForChild(e){return this.isFullyInitialized()&&!this.filtered_||this.node_.hasChild(e)}getNode(){return this.node_}}/**
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
 */class Xc{constructor(e){this.query_=e,this.index_=this.query_._queryParams.getIndex()}}function Zc(n,e,t,i){const s=[],r=[];return e.forEach(o=>{o.type==="child_changed"&&n.index_.indexedValueChanged(o.oldSnap,o.snapshotNode)&&r.push(Wc(o.childName,o.snapshotNode))}),gt(n,s,"child_removed",e,i,t),gt(n,s,"child_added",e,i,t),gt(n,s,"child_moved",r,i,t),gt(n,s,"child_changed",e,i,t),gt(n,s,"value",e,i,t),s}function gt(n,e,t,i,s,r){const o=i.filter(a=>a.type===t);o.sort((a,l)=>th(n,a,l)),o.forEach(a=>{const l=eh(n,a,r);s.forEach(c=>{c.respondsTo(a.type)&&e.push(c.createEvent(l,n.query_))})})}function eh(n,e,t){return e.type==="value"||e.type==="child_removed"||(e.prevName=t.getPredecessorChildName(e.childName,e.snapshotNode,n.index_)),e}function th(n,e,t){if(e.childName==null||t.childName==null)throw Be("Should only compare child_ events.");const i=new b(e.childName,e.snapshotNode),s=new b(t.childName,t.snapshotNode);return n.index_.compare(i,s)}/**
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
 */function Kt(n,e){return{eventCache:n,serverCache:e}}function yt(n,e,t,i){return Kt(new ge(e,t,i),n.serverCache)}function Ir(n,e,t,i){return Kt(n.eventCache,new ge(e,t,i))}function Qt(n){return n.eventCache.isFullyInitialized()?n.eventCache.getNode():null}function Oe(n){return n.serverCache.isFullyInitialized()?n.serverCache.getNode():null}/**
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
 */let hi;const nh=()=>(hi||(hi=new H(Bl)),hi);class A{static fromObject(e){let t=new A(null);return O(e,(i,s)=>{t=t.set(new E(i),s)}),t}constructor(e,t=nh()){this.value=e,this.children=t}isEmpty(){return this.value===null&&this.children.isEmpty()}findRootMostMatchingPathAndValue(e,t){if(this.value!=null&&t(this.value))return{path:w(),value:this.value};if(v(e))return null;{const i=y(e),s=this.children.get(i);if(s!==null){const r=s.findRootMostMatchingPathAndValue(x(e),t);return r!=null?{path:k(new E(i),r.path),value:r.value}:null}else return null}}findRootMostValueAndPath(e){return this.findRootMostMatchingPathAndValue(e,()=>!0)}subtree(e){if(v(e))return this;{const t=y(e),i=this.children.get(t);return i!==null?i.subtree(x(e)):new A(null)}}set(e,t){if(v(e))return new A(t,this.children);{const i=y(e),r=(this.children.get(i)||new A(null)).set(x(e),t),o=this.children.insert(i,r);return new A(this.value,o)}}remove(e){if(v(e))return this.children.isEmpty()?new A(null):new A(null,this.children);{const t=y(e),i=this.children.get(t);if(i){const s=i.remove(x(e));let r;return s.isEmpty()?r=this.children.remove(t):r=this.children.insert(t,s),this.value===null&&r.isEmpty()?new A(null):new A(this.value,r)}else return this}}get(e){if(v(e))return this.value;{const t=y(e),i=this.children.get(t);return i?i.get(x(e)):null}}setTree(e,t){if(v(e))return t;{const i=y(e),r=(this.children.get(i)||new A(null)).setTree(x(e),t);let o;return r.isEmpty()?o=this.children.remove(i):o=this.children.insert(i,r),new A(this.value,o)}}fold(e){return this.fold_(w(),e)}fold_(e,t){const i={};return this.children.inorderTraversal((s,r)=>{i[s]=r.fold_(k(e,s),t)}),t(e,this.value,i)}findOnPath(e,t){return this.findOnPath_(e,w(),t)}findOnPath_(e,t,i){const s=this.value?i(t,this.value):!1;if(s)return s;if(v(e))return null;{const r=y(e),o=this.children.get(r);return o?o.findOnPath_(x(e),k(t,r),i):null}}foreachOnPath(e,t){return this.foreachOnPath_(e,w(),t)}foreachOnPath_(e,t,i){if(v(e))return this;{this.value&&i(t,this.value);const s=y(e),r=this.children.get(s);return r?r.foreachOnPath_(x(e),k(t,s),i):new A(null)}}foreach(e){this.foreach_(w(),e)}foreach_(e,t){this.children.inorderTraversal((i,s)=>{s.foreach_(k(e,i),t)}),this.value&&t(e,this.value)}foreachChild(e){this.children.inorderTraversal((t,i)=>{i.value&&e(t,i.value)})}}/**
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
 */class Z{constructor(e){this.writeTree_=e}static empty(){return new Z(new A(null))}}function vt(n,e,t){if(v(e))return new Z(new A(t));{const i=n.writeTree_.findRootMostValueAndPath(e);if(i!=null){const s=i.path;let r=i.value;const o=W(s,e);return r=r.updateChild(o,t),new Z(n.writeTree_.set(s,r))}else{const s=new A(t),r=n.writeTree_.setTree(e,s);return new Z(r)}}}function di(n,e,t){let i=n;return O(t,(s,r)=>{i=vt(i,k(e,s),r)}),i}function xr(n,e){if(v(e))return Z.empty();{const t=n.writeTree_.setTree(e,new A(null));return new Z(t)}}function ui(n,e){return Me(n,e)!=null}function Me(n,e){const t=n.writeTree_.findRootMostValueAndPath(e);return t!=null?n.writeTree_.get(t.path).getChild(W(t.path,e)):null}function Tr(n){const e=[],t=n.writeTree_.value;return t!=null?t.isLeafNode()||t.forEachChild(T,(i,s)=>{e.push(new b(i,s))}):n.writeTree_.children.inorderTraversal((i,s)=>{s.value!=null&&e.push(new b(i,s.value))}),e}function ye(n,e){if(v(e))return n;{const t=Me(n,e);return t!=null?new Z(new A(t)):new Z(n.writeTree_.subtree(e))}}function fi(n){return n.writeTree_.isEmpty()}function Ye(n,e){return Ar(w(),n.writeTree_,e)}function Ar(n,e,t){if(e.value!=null)return t.updateChild(n,e.value);{let i=null;return e.children.inorderTraversal((s,r)=>{s===".priority"?(f(r.value!==null,"Priority writes must always be leaf nodes"),i=r.value):t=Ar(k(n,s),r,t)}),!t.getChild(n).isEmpty()&&i!==null&&(t=t.updateChild(k(n,".priority"),i)),t}}/**
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
 */function Yt(n,e){return Dr(e,n)}function ih(n,e,t,i,s){f(i>n.lastWriteId,"Stacking an older write on top of newer ones"),s===void 0&&(s=!0),n.allWrites.push({path:e,snap:t,writeId:i,visible:s}),s&&(n.visibleWrites=vt(n.visibleWrites,e,t)),n.lastWriteId=i}function sh(n,e,t,i){f(i>n.lastWriteId,"Stacking an older merge on top of newer ones"),n.allWrites.push({path:e,children:t,writeId:i,visible:!0}),n.visibleWrites=di(n.visibleWrites,e,t),n.lastWriteId=i}function rh(n,e){for(let t=0;t<n.allWrites.length;t++){const i=n.allWrites[t];if(i.writeId===e)return i}return null}function oh(n,e){const t=n.allWrites.findIndex(a=>a.writeId===e);f(t>=0,"removeWrite called with nonexistent writeId.");const i=n.allWrites[t];n.allWrites.splice(t,1);let s=i.visible,r=!1,o=n.allWrites.length-1;for(;s&&o>=0;){const a=n.allWrites[o];a.visible&&(o>=t&&ah(a,i.path)?s=!1:q(i.path,a.path)&&(r=!0)),o--}if(s){if(r)return lh(n),!0;if(i.snap)n.visibleWrites=xr(n.visibleWrites,i.path);else{const a=i.children;O(a,l=>{n.visibleWrites=xr(n.visibleWrites,k(i.path,l))})}return!0}else return!1}function ah(n,e){if(n.snap)return q(n.path,e);for(const t in n.children)if(n.children.hasOwnProperty(t)&&q(k(n.path,t),e))return!0;return!1}function lh(n){n.visibleWrites=kr(n.allWrites,ch,w()),n.allWrites.length>0?n.lastWriteId=n.allWrites[n.allWrites.length-1].writeId:n.lastWriteId=-1}function ch(n){return n.visible}function kr(n,e,t){let i=Z.empty();for(let s=0;s<n.length;++s){const r=n[s];if(e(r)){const o=r.path;let a;if(r.snap)q(t,o)?(a=W(t,o),i=vt(i,a,r.snap)):q(o,t)&&(a=W(o,t),i=vt(i,w(),r.snap.getChild(a)));else if(r.children){if(q(t,o))a=W(t,o),i=di(i,a,r.children);else if(q(o,t))if(a=W(o,t),v(a))i=di(i,w(),r.children);else{const l=Ie(r.children,y(a));if(l){const c=l.getChild(x(a));i=vt(i,w(),c)}}}else throw Be("WriteRecord should have .snap or .children")}}return i}function Nr(n,e,t,i,s){if(!i&&!s){const r=Me(n.visibleWrites,e);if(r!=null)return r;{const o=ye(n.visibleWrites,e);if(fi(o))return t;if(t==null&&!ui(o,w()))return null;{const a=t||m.EMPTY_NODE;return Ye(o,a)}}}else{const r=ye(n.visibleWrites,e);if(!s&&fi(r))return t;if(!s&&t==null&&!ui(r,w()))return null;{const o=function(c){return(c.visible||s)&&(!i||!~i.indexOf(c.writeId))&&(q(c.path,e)||q(e,c.path))},a=kr(n.allWrites,o,e),l=t||m.EMPTY_NODE;return Ye(a,l)}}}function hh(n,e,t){let i=m.EMPTY_NODE;const s=Me(n.visibleWrites,e);if(s)return s.isLeafNode()||s.forEachChild(T,(r,o)=>{i=i.updateImmediateChild(r,o)}),i;if(t){const r=ye(n.visibleWrites,e);return t.forEachChild(T,(o,a)=>{const l=Ye(ye(r,new E(o)),a);i=i.updateImmediateChild(o,l)}),Tr(r).forEach(o=>{i=i.updateImmediateChild(o.name,o.node)}),i}else{const r=ye(n.visibleWrites,e);return Tr(r).forEach(o=>{i=i.updateImmediateChild(o.name,o.node)}),i}}function dh(n,e,t,i,s){f(i||s,"Either existingEventSnap or existingServerSnap must exist");const r=k(e,t);if(ui(n.visibleWrites,r))return null;{const o=ye(n.visibleWrites,r);return fi(o)?s.getChild(t):Ye(o,s.getChild(t))}}function uh(n,e,t,i){const s=k(e,t),r=Me(n.visibleWrites,s);if(r!=null)return r;if(i.isCompleteForChild(t)){const o=ye(n.visibleWrites,s);return Ye(o,i.getNode().getImmediateChild(t))}else return null}function fh(n,e){return Me(n.visibleWrites,e)}function ph(n,e,t,i,s,r,o){let a;const l=ye(n.visibleWrites,e),c=Me(l,w());if(c!=null)a=c;else if(t!=null)a=Ye(l,t);else return[];if(a=a.withIndex(o),!a.isEmpty()&&!a.isLeafNode()){const d=[],h=o.getCompare(),u=r?a.getReverseIteratorFrom(i,o):a.getIteratorFrom(i,o);let p=u.getNext();for(;p&&d.length<s;)h(p,i)!==0&&d.push(p),p=u.getNext();return d}else return[]}function _h(){return{visibleWrites:Z.empty(),allWrites:[],lastWriteId:-1}}function Jt(n,e,t,i){return Nr(n.writeTree,n.treePath,e,t,i)}function pi(n,e){return hh(n.writeTree,n.treePath,e)}function Rr(n,e,t,i){return dh(n.writeTree,n.treePath,e,t,i)}function Xt(n,e){return fh(n.writeTree,k(n.treePath,e))}function mh(n,e,t,i,s,r){return ph(n.writeTree,n.treePath,e,t,i,s,r)}function _i(n,e,t){return uh(n.writeTree,n.treePath,e,t)}function Pr(n,e){return Dr(k(n.treePath,e),n.writeTree)}function Dr(n,e){return{treePath:n,writeTree:e}}/**
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
 */class gh{constructor(){this.changeMap=new Map}trackChildChange(e){const t=e.type,i=e.childName;f(t==="child_added"||t==="child_changed"||t==="child_removed","Only child changes supported for tracking"),f(i!==".priority","Only non-priority child changes can be tracked.");const s=this.changeMap.get(i);if(s){const r=s.type;if(t==="child_added"&&r==="child_removed")this.changeMap.set(i,pt(i,e.snapshotNode,s.snapshotNode));else if(t==="child_removed"&&r==="child_added")this.changeMap.delete(i);else if(t==="child_removed"&&r==="child_changed")this.changeMap.set(i,ft(i,s.oldSnap));else if(t==="child_changed"&&r==="child_added")this.changeMap.set(i,qe(i,e.snapshotNode));else if(t==="child_changed"&&r==="child_changed")this.changeMap.set(i,pt(i,e.snapshotNode,s.oldSnap));else throw Be("Illegal combination of changes: "+e+" occurred after "+s)}else this.changeMap.set(i,e)}getChanges(){return Array.from(this.changeMap.values())}}/**
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
 */class yh{getCompleteChild(e){return null}getChildAfterChild(e,t,i){return null}}const Or=new yh;class mi{constructor(e,t,i=null){this.writes_=e,this.viewCache_=t,this.optCompleteServerCache_=i}getCompleteChild(e){const t=this.viewCache_.eventCache;if(t.isCompleteForChild(e))return t.getNode().getImmediateChild(e);{const i=this.optCompleteServerCache_!=null?new ge(this.optCompleteServerCache_,!0,!1):this.viewCache_.serverCache;return _i(this.writes_,e,i)}}getChildAfterChild(e,t,i){const s=this.optCompleteServerCache_!=null?this.optCompleteServerCache_:Oe(this.viewCache_),r=mh(this.writes_,s,t,1,i,e);return r.length===0?null:r[0]}}/**
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
 */function vh(n){return{filter:n}}function bh(n,e){f(e.eventCache.getNode().isIndexed(n.filter.getIndex()),"Event snap not indexed"),f(e.serverCache.getNode().isIndexed(n.filter.getIndex()),"Server snap not indexed")}function wh(n,e,t,i,s){const r=new gh;let o,a;if(t.type===X.OVERWRITE){const c=t;c.source.fromUser?o=gi(n,e,c.path,c.snap,i,s,r):(f(c.source.fromServer,"Unknown source."),a=c.source.tagged||e.serverCache.isFiltered()&&!v(c.path),o=Zt(n,e,c.path,c.snap,i,s,a,r))}else if(t.type===X.MERGE){const c=t;c.source.fromUser?o=Eh(n,e,c.path,c.children,i,s,r):(f(c.source.fromServer,"Unknown source."),a=c.source.tagged||e.serverCache.isFiltered(),o=yi(n,e,c.path,c.children,i,s,a,r))}else if(t.type===X.ACK_USER_WRITE){const c=t;c.revert?o=xh(n,e,c.path,i,s,r):o=Sh(n,e,c.path,c.affectedTree,i,s,r)}else if(t.type===X.LISTEN_COMPLETE)o=Ih(n,e,t.path,i,r);else throw Be("Unknown operation type: "+t.type);const l=r.getChanges();return Ch(e,o,l),{viewCache:o,changes:l}}function Ch(n,e,t){const i=e.eventCache;if(i.isFullyInitialized()){const s=i.getNode().isLeafNode()||i.getNode().isEmpty(),r=Qt(n);(t.length>0||!n.eventCache.isFullyInitialized()||s&&!i.getNode().equals(r)||!i.getNode().getPriority().equals(r.getPriority()))&&t.push(wr(Qt(e)))}}function Mr(n,e,t,i,s,r){const o=e.eventCache;if(Xt(i,t)!=null)return e;{let a,l;if(v(t))if(f(e.serverCache.isFullyInitialized(),"If change path is empty, we must have complete server data"),e.serverCache.isFiltered()){const c=Oe(e),d=c instanceof m?c:m.EMPTY_NODE,h=pi(i,d);a=n.filter.updateFullNode(e.eventCache.getNode(),h,r)}else{const c=Jt(i,Oe(e));a=n.filter.updateFullNode(e.eventCache.getNode(),c,r)}else{const c=y(t);if(c===".priority"){f(me(t)===1,"Can't have a priority with additional path components");const d=o.getNode();l=e.serverCache.getNode();const h=Rr(i,t,d,l);h!=null?a=n.filter.updatePriority(d,h):a=o.getNode()}else{const d=x(t);let h;if(o.isCompleteForChild(c)){l=e.serverCache.getNode();const u=Rr(i,t,o.getNode(),l);u!=null?h=o.getNode().getImmediateChild(c).updateChild(d,u):h=o.getNode().getImmediateChild(c)}else h=_i(i,c,e.serverCache);h!=null?a=n.filter.updateChild(o.getNode(),c,h,d,s,r):a=o.getNode()}}return yt(e,a,o.isFullyInitialized()||v(t),n.filter.filtersNodes())}}function Zt(n,e,t,i,s,r,o,a){const l=e.serverCache;let c;const d=o?n.filter:n.filter.getIndexedFilter();if(v(t))c=d.updateFullNode(l.getNode(),i,null);else if(d.filtersNodes()&&!l.isFiltered()){const p=l.getNode().updateChild(t,i);c=d.updateFullNode(l.getNode(),p,null)}else{const p=y(t);if(!l.isCompleteForPath(t)&&me(t)>1)return e;const _=x(t),F=l.getNode().getImmediateChild(p).updateChild(_,i);p===".priority"?c=d.updatePriority(l.getNode(),F):c=d.updateChild(l.getNode(),p,F,_,Or,null)}const h=Ir(e,c,l.isFullyInitialized()||v(t),d.filtersNodes()),u=new mi(s,h,r);return Mr(n,h,t,s,u,a)}function gi(n,e,t,i,s,r,o){const a=e.eventCache;let l,c;const d=new mi(s,e,r);if(v(t))c=n.filter.updateFullNode(e.eventCache.getNode(),i,o),l=yt(e,c,!0,n.filter.filtersNodes());else{const h=y(t);if(h===".priority")c=n.filter.updatePriority(e.eventCache.getNode(),i),l=yt(e,c,a.isFullyInitialized(),a.isFiltered());else{const u=x(t),p=a.getNode().getImmediateChild(h);let _;if(v(u))_=i;else{const C=d.getCompleteChild(h);C!=null?Kn(u)===".priority"&&C.getChild(dr(u)).isEmpty()?_=C:_=C.updateChild(u,i):_=m.EMPTY_NODE}if(p.equals(_))l=e;else{const C=n.filter.updateChild(a.getNode(),h,_,u,d,o);l=yt(e,C,a.isFullyInitialized(),n.filter.filtersNodes())}}}return l}function Lr(n,e){return n.eventCache.isCompleteForChild(e)}function Eh(n,e,t,i,s,r,o){let a=e;return i.foreach((l,c)=>{const d=k(t,l);Lr(e,y(d))&&(a=gi(n,a,d,c,s,r,o))}),i.foreach((l,c)=>{const d=k(t,l);Lr(e,y(d))||(a=gi(n,a,d,c,s,r,o))}),a}function Fr(n,e,t){return t.foreach((i,s)=>{e=e.updateChild(i,s)}),e}function yi(n,e,t,i,s,r,o,a){if(e.serverCache.getNode().isEmpty()&&!e.serverCache.isFullyInitialized())return e;let l=e,c;v(t)?c=i:c=new A(null).setTree(t,i);const d=e.serverCache.getNode();return c.children.inorderTraversal((h,u)=>{if(d.hasChild(h)){const p=e.serverCache.getNode().getImmediateChild(h),_=Fr(n,p,u);l=Zt(n,l,new E(h),_,s,r,o,a)}}),c.children.inorderTraversal((h,u)=>{const p=!e.serverCache.isCompleteForChild(h)&&u.value===null;if(!d.hasChild(h)&&!p){const _=e.serverCache.getNode().getImmediateChild(h),C=Fr(n,_,u);l=Zt(n,l,new E(h),C,s,r,o,a)}}),l}function Sh(n,e,t,i,s,r,o){if(Xt(s,t)!=null)return e;const a=e.serverCache.isFiltered(),l=e.serverCache;if(i.value!=null){if(v(t)&&l.isFullyInitialized()||l.isCompleteForPath(t))return Zt(n,e,t,l.getNode().getChild(t),s,r,a,o);if(v(t)){let c=new A(null);return l.getNode().forEachChild(ne,(d,h)=>{c=c.set(new E(d),h)}),yi(n,e,t,c,s,r,a,o)}else return e}else{let c=new A(null);return i.foreach((d,h)=>{const u=k(t,d);l.isCompleteForPath(u)&&(c=c.set(d,l.getNode().getChild(u)))}),yi(n,e,t,c,s,r,a,o)}}function Ih(n,e,t,i,s){const r=e.serverCache,o=Ir(e,r.getNode(),r.isFullyInitialized()||v(t),r.isFiltered());return Mr(n,o,t,i,Or,s)}function xh(n,e,t,i,s,r){let o;if(Xt(i,t)!=null)return e;{const a=new mi(i,e,s),l=e.eventCache.getNode();let c;if(v(t)||y(t)===".priority"){let d;if(e.serverCache.isFullyInitialized())d=Jt(i,Oe(e));else{const h=e.serverCache.getNode();f(h instanceof m,"serverChildren would be complete if leaf node"),d=pi(i,h)}d=d,c=n.filter.updateFullNode(l,d,r)}else{const d=y(t);let h=_i(i,d,e.serverCache);h==null&&e.serverCache.isCompleteForChild(d)&&(h=l.getImmediateChild(d)),h!=null?c=n.filter.updateChild(l,d,h,x(t),a,r):e.eventCache.getNode().hasChild(d)?c=n.filter.updateChild(l,d,m.EMPTY_NODE,x(t),a,r):c=l,c.isEmpty()&&e.serverCache.isFullyInitialized()&&(o=Jt(i,Oe(e)),o.isLeafNode()&&(c=n.filter.updateFullNode(c,o,r)))}return o=e.serverCache.isFullyInitialized()||Xt(i,w())!=null,yt(e,c,o,n.filter.filtersNodes())}}/**
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
 */class Th{constructor(e,t){this.query_=e,this.eventRegistrations_=[];const i=this.query_._queryParams,s=new ni(i.getIndex()),r=$c(i);this.processor_=vh(r);const o=t.serverCache,a=t.eventCache,l=s.updateFullNode(m.EMPTY_NODE,o.getNode(),null),c=r.updateFullNode(m.EMPTY_NODE,a.getNode(),null),d=new ge(l,o.isFullyInitialized(),s.filtersNodes()),h=new ge(c,a.isFullyInitialized(),r.filtersNodes());this.viewCache_=Kt(h,d),this.eventGenerator_=new Xc(this.query_)}get query(){return this.query_}}function Ah(n){return n.viewCache_.serverCache.getNode()}function kh(n){return Qt(n.viewCache_)}function Nh(n,e){const t=Oe(n.viewCache_);return t&&(n.query._queryParams.loadsAllData()||!v(e)&&!t.getImmediateChild(y(e)).isEmpty())?t.getChild(e):null}function Br(n){return n.eventRegistrations_.length===0}function Rh(n,e){n.eventRegistrations_.push(e)}function Wr(n,e,t){const i=[];if(t){f(e==null,"A cancel should cancel all event registrations.");const s=n.query._path;n.eventRegistrations_.forEach(r=>{const o=r.createCancelEvent(t,s);o&&i.push(o)})}if(e){let s=[];for(let r=0;r<n.eventRegistrations_.length;++r){const o=n.eventRegistrations_[r];if(!o.matches(e))s.push(o);else if(e.hasAnyCallback()){s=s.concat(n.eventRegistrations_.slice(r+1));break}}n.eventRegistrations_=s}else n.eventRegistrations_=[];return i}function Ur(n,e,t,i){e.type===X.MERGE&&e.source.queryId!==null&&(f(Oe(n.viewCache_),"We should always have a full cache before handling merges"),f(Qt(n.viewCache_),"Missing event cache, even though we have a server cache"));const s=n.viewCache_,r=wh(n.processor_,s,e,t,i);return bh(n.processor_,r.viewCache),f(r.viewCache.serverCache.isFullyInitialized()||!s.serverCache.isFullyInitialized(),"Once a server snap is complete, it should never go back"),n.viewCache_=r.viewCache,$r(n,r.changes,r.viewCache.eventCache.getNode(),null)}function Ph(n,e){const t=n.viewCache_.eventCache,i=[];return t.getNode().isLeafNode()||t.getNode().forEachChild(T,(r,o)=>{i.push(qe(r,o))}),t.isFullyInitialized()&&i.push(wr(t.getNode())),$r(n,i,t.getNode(),e)}function $r(n,e,t,i){const s=i?[i]:n.eventRegistrations_;return Zc(n.eventGenerator_,e,t,s)}/**
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
 */let en;class Hr{constructor(){this.views=new Map}}function Dh(n){f(!en,"__referenceConstructor has already been defined"),en=n}function Oh(){return f(en,"Reference.ts has not been loaded"),en}function Mh(n){return n.views.size===0}function vi(n,e,t,i){const s=e.source.queryId;if(s!==null){const r=n.views.get(s);return f(r!=null,"SyncTree gave us an op for an invalid query."),Ur(r,e,t,i)}else{let r=[];for(const o of n.views.values())r=r.concat(Ur(o,e,t,i));return r}}function Vr(n,e,t,i,s){const r=e._queryIdentifier,o=n.views.get(r);if(!o){let a=Jt(t,s?i:null),l=!1;a?l=!0:i instanceof m?(a=pi(t,i),l=!1):(a=m.EMPTY_NODE,l=!1);const c=Kt(new ge(a,l,!1),new ge(i,s,!1));return new Th(e,c)}return o}function Lh(n,e,t,i,s,r){const o=Vr(n,e,i,s,r);return n.views.has(e._queryIdentifier)||n.views.set(e._queryIdentifier,o),Rh(o,t),Ph(o,t)}function Fh(n,e,t,i){const s=e._queryIdentifier,r=[];let o=[];const a=be(n);if(s==="default")for(const[l,c]of n.views.entries())o=o.concat(Wr(c,t,i)),Br(c)&&(n.views.delete(l),c.query._queryParams.loadsAllData()||r.push(c.query));else{const l=n.views.get(s);l&&(o=o.concat(Wr(l,t,i)),Br(l)&&(n.views.delete(s),l.query._queryParams.loadsAllData()||r.push(l.query)))}return a&&!be(n)&&r.push(new(Oh())(e._repo,e._path)),{removed:r,events:o}}function zr(n){const e=[];for(const t of n.views.values())t.query._queryParams.loadsAllData()||e.push(t);return e}function ve(n,e){let t=null;for(const i of n.views.values())t=t||Nh(i,e);return t}function jr(n,e){if(e._queryParams.loadsAllData())return tn(n);{const i=e._queryIdentifier;return n.views.get(i)}}function Gr(n,e){return jr(n,e)!=null}function be(n){return tn(n)!=null}function tn(n){for(const e of n.views.values())if(e.query._queryParams.loadsAllData())return e;return null}/**
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
 */let nn;function Bh(n){f(!nn,"__referenceConstructor has already been defined"),nn=n}function Wh(){return f(nn,"Reference.ts has not been loaded"),nn}let Uh=1;class qr{constructor(e){this.listenProvider_=e,this.syncPointTree_=new A(null),this.pendingWriteTree_=_h(),this.tagToQueryMap=new Map,this.queryToTagMap=new Map}}function bi(n,e,t,i,s){return ih(n.pendingWriteTree_,e,t,i,s),s?Je(n,new De(ai(),e,t)):[]}function $h(n,e,t,i){sh(n.pendingWriteTree_,e,t,i);const s=A.fromObject(t);return Je(n,new Qe(ai(),e,s))}function we(n,e,t=!1){const i=rh(n.pendingWriteTree_,e);if(oh(n.pendingWriteTree_,e)){let r=new A(null);return i.snap!=null?r=r.set(w(),!0):O(i.children,o=>{r=r.set(new E(o),!0)}),Je(n,new qt(i.path,r,t))}else return[]}function bt(n,e,t){return Je(n,new De(li(),e,t))}function Hh(n,e,t){const i=A.fromObject(t);return Je(n,new Qe(li(),e,i))}function Vh(n,e){return Je(n,new mt(li(),e))}function zh(n,e,t){const i=Ci(n,t);if(i){const s=Ei(i),r=s.path,o=s.queryId,a=W(r,e),l=new mt(ci(o),a);return Si(n,r,l)}else return[]}function sn(n,e,t,i,s=!1){const r=e._path,o=n.syncPointTree_.get(r);let a=[];if(o&&(e._queryIdentifier==="default"||Gr(o,e))){const l=Fh(o,e,t,i);Mh(o)&&(n.syncPointTree_=n.syncPointTree_.remove(r));const c=l.removed;if(a=l.events,!s){const d=c.findIndex(u=>u._queryParams.loadsAllData())!==-1,h=n.syncPointTree_.findOnPath(r,(u,p)=>be(p));if(d&&!h){const u=n.syncPointTree_.subtree(r);if(!u.isEmpty()){const p=qh(u);for(let _=0;_<p.length;++_){const C=p[_],F=C.query,et=Jr(n,C);n.listenProvider_.startListening(Ct(F),wt(n,F),et.hashFn,et.onComplete)}}}!h&&c.length>0&&!i&&(d?n.listenProvider_.stopListening(Ct(e),null):c.forEach(u=>{const p=n.queryToTagMap.get(on(u));n.listenProvider_.stopListening(Ct(u),p)}))}Kh(n,c)}return a}function Kr(n,e,t,i){const s=Ci(n,i);if(s!=null){const r=Ei(s),o=r.path,a=r.queryId,l=W(o,e),c=new De(ci(a),l,t);return Si(n,o,c)}else return[]}function jh(n,e,t,i){const s=Ci(n,i);if(s){const r=Ei(s),o=r.path,a=r.queryId,l=W(o,e),c=A.fromObject(t),d=new Qe(ci(a),l,c);return Si(n,o,d)}else return[]}function wi(n,e,t,i=!1){const s=e._path;let r=null,o=!1;n.syncPointTree_.foreachOnPath(s,(u,p)=>{const _=W(u,s);r=r||ve(p,_),o=o||be(p)});let a=n.syncPointTree_.get(s);a?(o=o||be(a),r=r||ve(a,w())):(a=new Hr,n.syncPointTree_=n.syncPointTree_.set(s,a));let l;r!=null?l=!0:(l=!1,r=m.EMPTY_NODE,n.syncPointTree_.subtree(s).foreachChild((p,_)=>{const C=ve(_,w());C&&(r=r.updateImmediateChild(p,C))}));const c=Gr(a,e);if(!c&&!e._queryParams.loadsAllData()){const u=on(e);f(!n.queryToTagMap.has(u),"View does not exist, but we have a tag");const p=Qh();n.queryToTagMap.set(u,p),n.tagToQueryMap.set(p,u)}const d=Yt(n.pendingWriteTree_,s);let h=Lh(a,e,t,d,r,l);if(!c&&!o&&!i){const u=jr(a,e);h=h.concat(Yh(n,e,u))}return h}function rn(n,e,t){const s=n.pendingWriteTree_,r=n.syncPointTree_.findOnPath(e,(o,a)=>{const l=W(o,e),c=ve(a,l);if(c)return c});return Nr(s,e,r,t,!0)}function Gh(n,e){const t=e._path;let i=null;n.syncPointTree_.foreachOnPath(t,(c,d)=>{const h=W(c,t);i=i||ve(d,h)});let s=n.syncPointTree_.get(t);s?i=i||ve(s,w()):(s=new Hr,n.syncPointTree_=n.syncPointTree_.set(t,s));const r=i!=null,o=r?new ge(i,!0,!1):null,a=Yt(n.pendingWriteTree_,e._path),l=Vr(s,e,a,r?o.getNode():m.EMPTY_NODE,r);return kh(l)}function Je(n,e){return Qr(e,n.syncPointTree_,null,Yt(n.pendingWriteTree_,w()))}function Qr(n,e,t,i){if(v(n.path))return Yr(n,e,t,i);{const s=e.get(w());t==null&&s!=null&&(t=ve(s,w()));let r=[];const o=y(n.path),a=n.operationForChild(o),l=e.children.get(o);if(l&&a){const c=t?t.getImmediateChild(o):null,d=Pr(i,o);r=r.concat(Qr(a,l,c,d))}return s&&(r=r.concat(vi(s,n,i,t))),r}}function Yr(n,e,t,i){const s=e.get(w());t==null&&s!=null&&(t=ve(s,w()));let r=[];return e.children.inorderTraversal((o,a)=>{const l=t?t.getImmediateChild(o):null,c=Pr(i,o),d=n.operationForChild(o);d&&(r=r.concat(Yr(d,a,l,c)))}),s&&(r=r.concat(vi(s,n,i,t))),r}function Jr(n,e){const t=e.query,i=wt(n,t);return{hashFn:()=>(Ah(e)||m.EMPTY_NODE).hash(),onComplete:s=>{if(s==="ok")return i?zh(n,t._path,i):Vh(n,t._path);{const r=$l(s,t);return sn(n,t,null,r)}}}}function wt(n,e){const t=on(e);return n.queryToTagMap.get(t)}function on(n){return n._path.toString()+"$"+n._queryIdentifier}function Ci(n,e){return n.tagToQueryMap.get(e)}function Ei(n){const e=n.indexOf("$");return f(e!==-1&&e<n.length-1,"Bad queryKey."),{queryId:n.substr(e+1),path:new E(n.substr(0,e))}}function Si(n,e,t){const i=n.syncPointTree_.get(e);f(i,"Missing sync point for query tag that we're tracking");const s=Yt(n.pendingWriteTree_,e);return vi(i,t,s,null)}function qh(n){return n.fold((e,t,i)=>{if(t&&be(t))return[tn(t)];{let s=[];return t&&(s=zr(t)),O(i,(r,o)=>{s=s.concat(o)}),s}})}function Ct(n){return n._queryParams.loadsAllData()&&!n._queryParams.isDefault()?new(Wh())(n._repo,n._path):n}function Kh(n,e){for(let t=0;t<e.length;++t){const i=e[t];if(!i._queryParams.loadsAllData()){const s=on(i),r=n.queryToTagMap.get(s);n.queryToTagMap.delete(s),n.tagToQueryMap.delete(r)}}}function Qh(){return Uh++}function Yh(n,e,t){const i=e._path,s=wt(n,e),r=Jr(n,t),o=n.listenProvider_.startListening(Ct(e),s,r.hashFn,r.onComplete),a=n.syncPointTree_.subtree(i);if(s)f(!be(a.value),"If we're adding a query, it shouldn't be shadowed");else{const l=a.fold((c,d,h)=>{if(!v(c)&&d&&be(d))return[tn(d).query];{let u=[];return d&&(u=u.concat(zr(d).map(p=>p.query))),O(h,(p,_)=>{u=u.concat(_)}),u}});for(let c=0;c<l.length;++c){const d=l[c];n.listenProvider_.stopListening(Ct(d),wt(n,d))}}return o}/**
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
 */class Ii{constructor(e){this.node_=e}getImmediateChild(e){const t=this.node_.getImmediateChild(e);return new Ii(t)}node(){return this.node_}}class xi{constructor(e,t){this.syncTree_=e,this.path_=t}getImmediateChild(e){const t=k(this.path_,e);return new xi(this.syncTree_,t)}node(){return rn(this.syncTree_,this.path_)}}const Jh=function(n){return n=n||{},n.timestamp=n.timestamp||new Date().getTime(),n},Xr=function(n,e,t){if(!n||typeof n!="object")return n;if(f(".sv"in n,"Unexpected leaf node or priority contents"),typeof n[".sv"]=="string")return Xh(n[".sv"],e,t);if(typeof n[".sv"]=="object")return Zh(n[".sv"],e);f(!1,"Unexpected server value: "+JSON.stringify(n,null,2))},Xh=function(n,e,t){switch(n){case"timestamp":return t.timestamp;default:f(!1,"Unexpected server value: "+n)}},Zh=function(n,e,t){n.hasOwnProperty("increment")||f(!1,"Unexpected server value: "+JSON.stringify(n,null,2));const i=n.increment;typeof i!="number"&&f(!1,"Unexpected increment value: "+i);const s=e.node();if(f(s!==null&&typeof s<"u","Expected ChildrenNode.EMPTY_NODE for nulls"),!s.isLeafNode())return i;const o=s.getValue();return typeof o!="number"?i:o+i},Zr=function(n,e,t,i){return Ai(e,new xi(t,n),i)},Ti=function(n,e,t){return Ai(n,new Ii(e),t)};function Ai(n,e,t){const i=n.getPriority().val(),s=Xr(i,e.getImmediateChild(".priority"),t);let r;if(n.isLeafNode()){const o=n,a=Xr(o.getValue(),e,t);return a!==o.getValue()||s!==o.getPriority().val()?new L(a,N(s)):n}else{const o=n;return r=o,s!==o.getPriority().val()&&(r=r.updatePriority(new L(s))),o.forEachChild(T,(a,l)=>{const c=Ai(l,e.getImmediateChild(a),t);c!==l&&(r=r.updateImmediateChild(a,c))}),r}}/**
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
 */class ki{constructor(e="",t=null,i={children:{},childCount:0}){this.name=e,this.parent=t,this.node=i}}function an(n,e){let t=e instanceof E?e:new E(e),i=n,s=y(t);for(;s!==null;){const r=Ie(i.node.children,s)||{children:{},childCount:0};i=new ki(s,i,r),t=x(t),s=y(t)}return i}function Le(n){return n.node.value}function Ni(n,e){n.node.value=e,Ri(n)}function eo(n){return n.node.childCount>0}function ed(n){return Le(n)===void 0&&!eo(n)}function ln(n,e){O(n.node.children,(t,i)=>{e(new ki(t,n,i))})}function to(n,e,t,i){t&&e(n),ln(n,s=>{to(s,e,!0)})}function td(n,e,t){let i=n.parent;for(;i!==null;){if(e(i))return!0;i=i.parent}return!1}function Et(n){return new E(n.parent===null?n.name:Et(n.parent)+"/"+n.name)}function Ri(n){n.parent!==null&&nd(n.parent,n.name,n)}function nd(n,e,t){const i=ed(t),s=J(n.node.children,e);i&&s?(delete n.node.children[e],n.node.childCount--,Ri(n)):!i&&!s&&(n.node.children[e]=t.node,n.node.childCount++,Ri(n))}/**
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
 */const id=/[\[\].#$\/\u0000-\u001F\u007F]/,sd=/[\[\].#$\u0000-\u001F\u007F]/,Pi=10*1024*1024,cn=function(n){return typeof n=="string"&&n.length!==0&&!id.test(n)},no=function(n){return typeof n=="string"&&n.length!==0&&!sd.test(n)},rd=function(n){return n&&(n=n.replace(/^\/*\.info(\/|$)/,"/")),no(n)},St=function(n){return n===null||typeof n=="string"||typeof n=="number"&&!Lt(n)||n&&typeof n=="object"&&J(n,".sv")},ie=function(n,e,t,i){i&&e===void 0||It(xe(n,"value"),e,t)},It=function(n,e,t){const i=t instanceof E?new wc(t,n):t;if(e===void 0)throw new Error(n+"contains undefined "+Pe(i));if(typeof e=="function")throw new Error(n+"contains a function "+Pe(i)+" with contents = "+e.toString());if(Lt(e))throw new Error(n+"contains "+e.toString()+" "+Pe(i));if(typeof e=="string"&&e.length>Pi/3&&Ot(e)>Pi)throw new Error(n+"contains a string greater than "+Pi+" utf8 bytes "+Pe(i)+" ('"+e.substring(0,50)+"...')");if(e&&typeof e=="object"){let s=!1,r=!1;if(O(e,(o,a)=>{if(o===".value")s=!0;else if(o!==".priority"&&o!==".sv"&&(r=!0,!cn(o)))throw new Error(n+" contains an invalid key ("+o+") "+Pe(i)+`.  Keys must be non-empty strings and can't contain ".", "#", "$", "/", "[", or "]"`);Cc(i,o),It(n,a,i),Ec(i)}),s&&r)throw new Error(n+' contains ".value" child '+Pe(i)+" in addition to actual children.")}},od=function(n,e){let t,i;for(t=0;t<e.length;t++){i=e[t];const r=ct(i);for(let o=0;o<r.length;o++)if(!(r[o]===".priority"&&o===r.length-1)){if(!cn(r[o]))throw new Error(n+"contains an invalid key ("+r[o]+") in path "+i.toString()+`. Keys must be non-empty strings and can't contain ".", "#", "$", "/", "[", or "]"`)}}e.sort(bc);let s=null;for(t=0;t<e.length;t++){if(i=e[t],s!==null&&q(s,i))throw new Error(n+"contains a path "+s.toString()+" that is ancestor of another path "+i.toString());s=i}},io=function(n,e,t,i){const s=xe(n,"values");if(!(e&&typeof e=="object")||Array.isArray(e))throw new Error(s+" must be an object containing the children to replace.");const r=[];O(e,(o,a)=>{const l=new E(o);if(It(s,a,k(t,l)),Kn(l)===".priority"&&!St(a))throw new Error(s+"contains an invalid value for '"+l.toString()+"', which must be a valid Firebase priority (a string, finite number, server value, or null).");r.push(l)}),od(s,r)},Di=function(n,e,t){if(Lt(e))throw new Error(xe(n,"priority")+"is "+e.toString()+", but must be a valid Firebase priority (a string, finite number, server value, or null).");if(!St(e))throw new Error(xe(n,"priority")+"must be a valid Firebase priority (a string, finite number, server value, or null).")},xt=function(n,e,t,i){if(t!==void 0&&!cn(t))throw new Error(xe(n,e)+'was an invalid key = "'+t+`".  Firebase keys must be non-empty strings and can't contain ".", "#", "$", "/", "[", or "]").`)},hn=function(n,e,t,i){if(!(i&&t===void 0)&&!no(t))throw new Error(xe(n,e)+'was an invalid path = "'+t+`". Paths must be non-empty strings and can't contain ".", "#", "$", "[", or "]"`)},ad=function(n,e,t,i){t&&(t=t.replace(/^\/*\.info(\/|$)/,"/")),hn(n,e,t,i)},ee=function(n,e){if(y(e)===".info")throw new Error(n+" failed = Can't modify data under /.info/")},so=function(n,e){const t=e.path.toString();if(typeof e.repoInfo.host!="string"||e.repoInfo.host.length===0||!cn(e.repoInfo.namespace)&&e.repoInfo.host.split(":")[0]!=="localhost"||t.length!==0&&!rd(t))throw new Error(xe(n,"url")+`must be a valid firebase URL and the path can't contain ".", "#", "$", "[", or "]".`)};/**
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
 */class ld{constructor(){this.eventLists_=[],this.recursionDepth_=0}}function dn(n,e){let t=null;for(let i=0;i<e.length;i++){const s=e[i],r=s.getPath();t!==null&&!Qn(r,t.path)&&(n.eventLists_.push(t),t=null),t===null&&(t={events:[],path:r}),t.events.push(s)}t&&n.eventLists_.push(t)}function ro(n,e,t){dn(n,t),oo(n,i=>Qn(i,e))}function j(n,e,t){dn(n,t),oo(n,i=>q(i,e)||q(e,i))}function oo(n,e){n.recursionDepth_++;let t=!0;for(let i=0;i<n.eventLists_.length;i++){const s=n.eventLists_[i];if(s){const r=s.path;e(r)?(cd(n.eventLists_[i]),n.eventLists_[i]=null):t=!1}}t&&(n.eventLists_=[]),n.recursionDepth_--}function cd(n){for(let e=0;e<n.events.length;e++){const t=n.events[e];if(t!==null){n.events[e]=null;const i=t.getEventRunner();Ne&&D("event: "+t.toString()),Ve(i)}}}/**
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
 */const ao="repo_interrupt",hd=25;class dd{constructor(e,t,i,s){this.repoInfo_=e,this.forceRestClient_=t,this.authTokenProvider_=i,this.appCheckProvider_=s,this.dataUpdateCount=0,this.statsListener_=null,this.eventQueue_=new ld,this.nextWriteId_=1,this.interceptServerDataCallback_=null,this.onDisconnect_=Gt(),this.transactionQueueTree_=new ki,this.persistentConnection_=null,this.key=this.repoInfo_.toURLString()}toString(){return(this.repoInfo_.secure?"https://":"http://")+this.repoInfo_.host}}function ud(n,e,t){if(n.stats_=jn(n.repoInfo_),n.forceRestClient_||jl())n.server_=new jt(n.repoInfo_,(i,s,r,o)=>{co(n,i,s,r,o)},n.authTokenProvider_,n.appCheckProvider_),setTimeout(()=>ho(n,!0),0);else{if(typeof t<"u"&&t!==null){if(typeof t!="object")throw new Error("Only objects are supported for option databaseAuthVariableOverride");try{P(t)}catch(i){throw new Error("Invalid authOverride provided: "+i)}}n.persistentConnection_=new K(n.repoInfo_,e,(i,s,r,o)=>{co(n,i,s,r,o)},i=>{ho(n,i)},i=>{fd(n,i)},n.authTokenProvider_,n.appCheckProvider_,t),n.server_=n.persistentConnection_}n.authTokenProvider_.addTokenChangeListener(i=>{n.server_.refreshAuthToken(i)}),n.appCheckProvider_.addTokenChangeListener(i=>{n.server_.refreshAppCheckToken(i.token)}),n.statsReporter_=Yl(n.repoInfo_,()=>new Jc(n.stats_,n.server_)),n.infoData_=new Gc,n.infoSyncTree_=new qr({startListening:(i,s,r,o)=>{let a=[];const l=n.infoData_.getNode(i._path);return l.isEmpty()||(a=bt(n.infoSyncTree_,i._path,l),setTimeout(()=>{o("ok")},0)),a},stopListening:()=>{}}),Oi(n,"connected",!1),n.serverSyncTree_=new qr({startListening:(i,s,r,o)=>(n.server_.listen(i,r,s,(a,l)=>{const c=o(a,l);j(n.eventQueue_,i._path,c)}),[]),stopListening:(i,s)=>{n.server_.unlisten(i,s)}})}function lo(n){const t=n.infoData_.getNode(new E(".info/serverTimeOffset")).val()||0;return new Date().getTime()+t}function Tt(n){return Jh({timestamp:lo(n)})}function co(n,e,t,i,s){n.dataUpdateCount++;const r=new E(e);t=n.interceptServerDataCallback_?n.interceptServerDataCallback_(e,t):t;let o=[];if(s)if(i){const l=Pt(t,c=>N(c));o=jh(n.serverSyncTree_,r,l,s)}else{const l=N(t);o=Kr(n.serverSyncTree_,r,l,s)}else if(i){const l=Pt(t,c=>N(c));o=Hh(n.serverSyncTree_,r,l)}else{const l=N(t);o=bt(n.serverSyncTree_,r,l)}let a=r;o.length>0&&(a=Ze(n,r)),j(n.eventQueue_,a,o)}function ho(n,e){Oi(n,"connected",e),e===!1&&md(n)}function fd(n,e){O(e,(t,i)=>{Oi(n,t,i)})}function Oi(n,e,t){const i=new E("/.info/"+e),s=N(t);n.infoData_.updateSnapshot(i,s);const r=bt(n.infoSyncTree_,i,s);j(n.eventQueue_,i,r)}function un(n){return n.nextWriteId_++}function pd(n,e,t){const i=Gh(n.serverSyncTree_,e);return i!=null?Promise.resolve(i):n.server_.get(e).then(s=>{const r=N(s).withIndex(e._queryParams.getIndex());wi(n.serverSyncTree_,e,t,!0);let o;if(e._queryParams.loadsAllData())o=bt(n.serverSyncTree_,e._path,r);else{const a=wt(n.serverSyncTree_,e);o=Kr(n.serverSyncTree_,e._path,r,a)}return j(n.eventQueue_,e._path,o),sn(n.serverSyncTree_,e,t,null,!0),r},s=>(Xe(n,"get for query "+P(e)+" failed: "+s),Promise.reject(new Error(s))))}function Mi(n,e,t,i,s){Xe(n,"set",{path:e.toString(),value:t,priority:i});const r=Tt(n),o=N(t,i),a=rn(n.serverSyncTree_,e),l=Ti(o,a,r),c=un(n),d=bi(n.serverSyncTree_,e,l,c,!0);dn(n.eventQueue_,d),n.server_.put(e.toString(),o.val(!0),(u,p)=>{const _=u==="ok";_||B("set at "+e+" failed: "+u);const C=we(n.serverSyncTree_,c,!_);j(n.eventQueue_,e,C),Ce(n,s,u,p)});const h=Bi(n,e);Ze(n,h),j(n.eventQueue_,h,[])}function _d(n,e,t,i){Xe(n,"update",{path:e.toString(),value:t});let s=!0;const r=Tt(n),o={};if(O(t,(a,l)=>{s=!1,o[a]=Zr(k(e,a),N(l),n.serverSyncTree_,r)}),s)D("update() called with empty data.  Don't do anything."),Ce(n,i,"ok",void 0);else{const a=un(n),l=$h(n.serverSyncTree_,e,o,a);dn(n.eventQueue_,l),n.server_.merge(e.toString(),t,(c,d)=>{const h=c==="ok";h||B("update at "+e+" failed: "+c);const u=we(n.serverSyncTree_,a,!h),p=u.length>0?Ze(n,e):e;j(n.eventQueue_,p,u),Ce(n,i,c,d)}),O(t,c=>{const d=Bi(n,k(e,c));Ze(n,d)}),j(n.eventQueue_,e,[])}}function md(n){Xe(n,"onDisconnectEvents");const e=Tt(n),t=Gt();oi(n.onDisconnect_,w(),(s,r)=>{const o=Zr(s,r,n.serverSyncTree_,e);Ke(t,s,o)});let i=[];oi(t,w(),(s,r)=>{i=i.concat(bt(n.serverSyncTree_,s,r));const o=Bi(n,s);Ze(n,o)}),n.onDisconnect_=Gt(),j(n.eventQueue_,w(),i)}function gd(n,e,t){n.server_.onDisconnectCancel(e.toString(),(i,s)=>{i==="ok"&&ri(n.onDisconnect_,e),Ce(n,t,i,s)})}function uo(n,e,t,i){const s=N(t);n.server_.onDisconnectPut(e.toString(),s.val(!0),(r,o)=>{r==="ok"&&Ke(n.onDisconnect_,e,s),Ce(n,i,r,o)})}function yd(n,e,t,i,s){const r=N(t,i);n.server_.onDisconnectPut(e.toString(),r.val(!0),(o,a)=>{o==="ok"&&Ke(n.onDisconnect_,e,r),Ce(n,s,o,a)})}function vd(n,e,t,i){if(Cn(t)){D("onDisconnect().update() called with empty data.  Don't do anything."),Ce(n,i,"ok",void 0);return}n.server_.onDisconnectMerge(e.toString(),t,(s,r)=>{s==="ok"&&O(t,(o,a)=>{const l=N(a);Ke(n.onDisconnect_,k(e,o),l)}),Ce(n,i,s,r)})}function bd(n,e,t){let i;y(e._path)===".info"?i=wi(n.infoSyncTree_,e,t):i=wi(n.serverSyncTree_,e,t),ro(n.eventQueue_,e._path,i)}function Li(n,e,t){let i;y(e._path)===".info"?i=sn(n.infoSyncTree_,e,t):i=sn(n.serverSyncTree_,e,t),ro(n.eventQueue_,e._path,i)}function fo(n){n.persistentConnection_&&n.persistentConnection_.interrupt(ao)}function wd(n){n.persistentConnection_&&n.persistentConnection_.resume(ao)}function Xe(n,...e){let t="";n.persistentConnection_&&(t=n.persistentConnection_.id+":"),D(t,...e)}function Ce(n,e,t,i){e&&Ve(()=>{if(t==="ok")e(null);else{const s=(t||"error").toUpperCase();let r=s;i&&(r+=": "+i);const o=new Error(r);o.code=s,e(o)}})}function Cd(n,e,t,i,s,r){Xe(n,"transaction on "+e);const o={path:e,update:t,onComplete:i,status:null,order:Rs(),applyLocally:r,retryCount:0,unwatcher:s,abortReason:null,currentWriteId:null,currentInputSnapshot:null,currentOutputSnapshotRaw:null,currentOutputSnapshotResolved:null},a=Fi(n,e,void 0);o.currentInputSnapshot=a;const l=o.update(a.val());if(l===void 0)o.unwatcher(),o.currentOutputSnapshotRaw=null,o.currentOutputSnapshotResolved=null,o.onComplete&&o.onComplete(null,!1,o.currentInputSnapshot);else{It("transaction failed: Data returned ",l,o.path),o.status=0;const c=an(n.transactionQueueTree_,e),d=Le(c)||[];d.push(o),Ni(c,d);let h;typeof l=="object"&&l!==null&&J(l,".priority")?(h=Ie(l,".priority"),f(St(h),"Invalid priority returned by transaction. Priority must be a valid string, finite number, server value, or null.")):h=(rn(n.serverSyncTree_,e)||m.EMPTY_NODE).getPriority().val();const u=Tt(n),p=N(l,h),_=Ti(p,a,u);o.currentOutputSnapshotRaw=p,o.currentOutputSnapshotResolved=_,o.currentWriteId=un(n);const C=bi(n.serverSyncTree_,e,_,o.currentWriteId,o.applyLocally);j(n.eventQueue_,e,C),fn(n,n.transactionQueueTree_)}}function Fi(n,e,t){return rn(n.serverSyncTree_,e,t)||m.EMPTY_NODE}function fn(n,e=n.transactionQueueTree_){if(e||pn(n,e),Le(e)){const t=_o(n,e);f(t.length>0,"Sending zero length transaction queue"),t.every(s=>s.status===0)&&Ed(n,Et(e),t)}else eo(e)&&ln(e,t=>{fn(n,t)})}function Ed(n,e,t){const i=t.map(c=>c.currentWriteId),s=Fi(n,e,i);let r=s;const o=s.hash();for(let c=0;c<t.length;c++){const d=t[c];f(d.status===0,"tryToSendTransactionQueue_: items in queue should all be run."),d.status=1,d.retryCount++;const h=W(e,d.path);r=r.updateChild(h,d.currentOutputSnapshotRaw)}const a=r.val(!0),l=e;n.server_.put(l.toString(),a,c=>{Xe(n,"transaction put response",{path:l.toString(),status:c});let d=[];if(c==="ok"){const h=[];for(let u=0;u<t.length;u++)t[u].status=2,d=d.concat(we(n.serverSyncTree_,t[u].currentWriteId)),t[u].onComplete&&h.push(()=>t[u].onComplete(null,!0,t[u].currentOutputSnapshotResolved)),t[u].unwatcher();pn(n,an(n.transactionQueueTree_,e)),fn(n,n.transactionQueueTree_),j(n.eventQueue_,e,d);for(let u=0;u<h.length;u++)Ve(h[u])}else{if(c==="datastale")for(let h=0;h<t.length;h++)t[h].status===3?t[h].status=4:t[h].status=0;else{B("transaction at "+l.toString()+" failed: "+c);for(let h=0;h<t.length;h++)t[h].status=4,t[h].abortReason=c}Ze(n,e)}},o)}function Ze(n,e){const t=po(n,e),i=Et(t),s=_o(n,t);return Sd(n,s,i),i}function Sd(n,e,t){if(e.length===0)return;const i=[];let s=[];const o=e.filter(a=>a.status===0).map(a=>a.currentWriteId);for(let a=0;a<e.length;a++){const l=e[a],c=W(t,l.path);let d=!1,h;if(f(c!==null,"rerunTransactionsUnderNode_: relativePath should not be null."),l.status===4)d=!0,h=l.abortReason,s=s.concat(we(n.serverSyncTree_,l.currentWriteId,!0));else if(l.status===0)if(l.retryCount>=hd)d=!0,h="maxretry",s=s.concat(we(n.serverSyncTree_,l.currentWriteId,!0));else{const u=Fi(n,l.path,o);l.currentInputSnapshot=u;const p=e[a].update(u.val());if(p!==void 0){It("transaction failed: Data returned ",p,l.path);let _=N(p);typeof p=="object"&&p!=null&&J(p,".priority")||(_=_.updatePriority(u.getPriority()));const F=l.currentWriteId,et=Tt(n),yn=Ti(_,u,et);l.currentOutputSnapshotRaw=_,l.currentOutputSnapshotResolved=yn,l.currentWriteId=un(n),o.splice(o.indexOf(F),1),s=s.concat(bi(n.serverSyncTree_,l.path,yn,l.currentWriteId,l.applyLocally)),s=s.concat(we(n.serverSyncTree_,F,!0))}else d=!0,h="nodata",s=s.concat(we(n.serverSyncTree_,l.currentWriteId,!0))}j(n.eventQueue_,t,s),s=[],d&&(e[a].status=2,(function(u){setTimeout(u,Math.floor(0))})(e[a].unwatcher),e[a].onComplete&&(h==="nodata"?i.push(()=>e[a].onComplete(null,!1,e[a].currentInputSnapshot)):i.push(()=>e[a].onComplete(new Error(h),!1,null))))}pn(n,n.transactionQueueTree_);for(let a=0;a<i.length;a++)Ve(i[a]);fn(n,n.transactionQueueTree_)}function po(n,e){let t,i=n.transactionQueueTree_;for(t=y(e);t!==null&&Le(i)===void 0;)i=an(i,t),e=x(e),t=y(e);return i}function _o(n,e){const t=[];return mo(n,e,t),t.sort((i,s)=>i.order-s.order),t}function mo(n,e,t){const i=Le(e);if(i)for(let s=0;s<i.length;s++)t.push(i[s]);ln(e,s=>{mo(n,s,t)})}function pn(n,e){const t=Le(e);if(t){let i=0;for(let s=0;s<t.length;s++)t[s].status!==2&&(t[i]=t[s],i++);t.length=i,Ni(e,t.length>0?t:void 0)}ln(e,i=>{pn(n,i)})}function Bi(n,e){const t=Et(po(n,e)),i=an(n.transactionQueueTree_,e);return td(i,s=>{Wi(n,s)}),Wi(n,i),to(i,s=>{Wi(n,s)}),t}function Wi(n,e){const t=Le(e);if(t){const i=[];let s=[],r=-1;for(let o=0;o<t.length;o++)t[o].status===3||(t[o].status===1?(f(r===o-1,"All SENT items should be at beginning of queue."),r=o,t[o].status=3,t[o].abortReason="set"):(f(t[o].status===0,"Unexpected transaction status in abort"),t[o].unwatcher(),s=s.concat(we(n.serverSyncTree_,t[o].currentWriteId,!0)),t[o].onComplete&&i.push(t[o].onComplete.bind(null,new Error("set"),!1,null))));r===-1?Ni(e,void 0):t.length=r+1,j(n.eventQueue_,Et(e),s);for(let o=0;o<i.length;o++)Ve(i[o])}}/**
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
 */function Id(n){let e="";const t=n.split("/");for(let i=0;i<t.length;i++)if(t[i].length>0){let s=t[i];try{s=decodeURIComponent(s.replace(/\+/g," "))}catch{}e+="/"+s}return e}function xd(n){const e={};n.charAt(0)==="?"&&(n=n.substring(1));for(const t of n.split("&")){if(t.length===0)continue;const i=t.split("=");i.length===2?e[decodeURIComponent(i[0])]=decodeURIComponent(i[1]):B(`Invalid query segment '${t}' in query '${n}'`)}return e}const Ui=function(n,e){const t=Td(n),i=t.namespace;t.domain==="firebase.com"&&te(t.host+" is no longer supported. Please use <YOUR FIREBASE>.firebaseio.com instead"),(!i||i==="undefined")&&t.domain!=="localhost"&&te("Cannot parse Firebase url. Please use https://<YOUR FIREBASE>.firebaseio.com"),t.secure||Ll();const s=t.scheme==="ws"||t.scheme==="wss";return{repoInfo:new qs(t.host,t.secure,i,s,e,"",i!==t.subdomain),path:new E(t.pathString)}},Td=function(n){let e="",t="",i="",s="",r="",o=!0,a="https",l=443;if(typeof n=="string"){let c=n.indexOf("//");c>=0&&(a=n.substring(0,c-1),n=n.substring(c+2));let d=n.indexOf("/");d===-1&&(d=n.length);let h=n.indexOf("?");h===-1&&(h=n.length),e=n.substring(0,Math.min(d,h)),d<h&&(s=Id(n.substring(d,h)));const u=xd(n.substring(Math.min(n.length,h)));c=e.indexOf(":"),c>=0?(o=a==="https"||a==="wss",l=parseInt(e.substring(c+1),10)):c=e.length;const p=e.slice(0,c);if(p.toLowerCase()==="localhost")t="localhost";else if(p.split(".").length<=2)t=p;else{const _=e.indexOf(".");i=e.substring(0,_).toLowerCase(),t=e.substring(_+1),r=i}"ns"in u&&(r=u.ns)}return{host:e,port:l,domain:t,subdomain:i,secure:o,scheme:a,pathString:s,namespace:r}};/**
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
 */const go="-0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz",Ad=(function(){let n=0;const e=[];return function(t){const i=t===n;n=t;let s;const r=new Array(8);for(s=7;s>=0;s--)r[s]=go.charAt(t%64),t=Math.floor(t/64);f(t===0,"Cannot push at time == 0");let o=r.join("");if(i){for(s=11;s>=0&&e[s]===63;s--)e[s]=0;e[s]++}else for(s=0;s<12;s++)e[s]=Math.floor(Math.random()*64);for(s=0;s<12;s++)o+=go.charAt(e[s]);return f(o.length===20,"nextPushId: Length should be 20."),o}})();/**
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
 */class yo{constructor(e,t,i,s){this.eventType=e,this.eventRegistration=t,this.snapshot=i,this.prevName=s}getPath(){const e=this.snapshot.ref;return this.eventType==="value"?e._path:e.parent._path}getEventType(){return this.eventType}getEventRunner(){return this.eventRegistration.getEventRunner(this)}toString(){return this.getPath().toString()+":"+this.eventType+":"+P(this.snapshot.exportVal())}}class vo{constructor(e,t,i){this.eventRegistration=e,this.error=t,this.path=i}getPath(){return this.path}getEventType(){return"cancel"}getEventRunner(){return this.eventRegistration.getEventRunner(this)}toString(){return this.path.toString()+":cancel"}}/**
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
 */class $i{constructor(e,t){this.snapshotCallback=e,this.cancelCallback=t}onValue(e,t){this.snapshotCallback.call(null,e,t)}onCancel(e){return f(this.hasCancelCallback,"Raising a cancel event on a listener with no cancel callback"),this.cancelCallback.call(null,e)}get hasCancelCallback(){return!!this.cancelCallback}matches(e){return this.snapshotCallback===e.snapshotCallback||this.snapshotCallback.userCallback!==void 0&&this.snapshotCallback.userCallback===e.snapshotCallback.userCallback&&this.snapshotCallback.context===e.snapshotCallback.context}}/**
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
 */class bo{constructor(e,t){this._repo=e,this._path=t}cancel(){const e=new z;return gd(this._repo,this._path,e.wrapCallback(()=>{})),e.promise}remove(){ee("OnDisconnect.remove",this._path);const e=new z;return uo(this._repo,this._path,null,e.wrapCallback(()=>{})),e.promise}set(e){ee("OnDisconnect.set",this._path),ie("OnDisconnect.set",e,this._path,!1);const t=new z;return uo(this._repo,this._path,e,t.wrapCallback(()=>{})),t.promise}setWithPriority(e,t){ee("OnDisconnect.setWithPriority",this._path),ie("OnDisconnect.setWithPriority",e,this._path,!1),Di("OnDisconnect.setWithPriority",t);const i=new z;return yd(this._repo,this._path,e,t,i.wrapCallback(()=>{})),i.promise}update(e){ee("OnDisconnect.update",this._path),io("OnDisconnect.update",e,this._path);const t=new z;return vd(this._repo,this._path,e,t.wrapCallback(()=>{})),t.promise}}/**
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
 */class V{constructor(e,t,i,s){this._repo=e,this._path=t,this._queryParams=i,this._orderByCalled=s}get key(){return v(this._path)?null:Kn(this._path)}get ref(){return new Q(this._repo,this._path)}get _queryIdentifier(){const e=Er(this._queryParams),t=Un(e);return t==="{}"?"default":t}get _queryObject(){return Er(this._queryParams)}isEqual(e){if(e=U(e),!(e instanceof V))return!1;const t=this._repo===e._repo,i=Qn(this._path,e._path),s=this._queryIdentifier===e._queryIdentifier;return t&&i&&s}toJSON(){return this.toString()}toString(){return this._repo.toString()+vc(this._path)}}function _n(n,e){if(n._orderByCalled===!0)throw new Error(e+": You can't combine multiple orderBy calls.")}function Ee(n){let e=null,t=null;if(n.hasStart()&&(e=n.getIndexStartValue()),n.hasEnd()&&(t=n.getIndexEndValue()),n.getIndex()===ne){const i="Query: When ordering by key, you may only pass one argument to startAt(), endAt(), or equalTo().",s="Query: When ordering by key, the argument passed to startAt(), startAfter(), endAt(), endBefore(), or equalTo() must be a string.";if(n.hasStart()){if(n.getIndexStartName()!==pe)throw new Error(i);if(typeof e!="string")throw new Error(s)}if(n.hasEnd()){if(n.getIndexEndName()!==ce)throw new Error(i);if(typeof t!="string")throw new Error(s)}}else if(n.getIndex()===T){if(e!=null&&!St(e)||t!=null&&!St(t))throw new Error("Query: When ordering by priority, the first argument passed to startAt(), startAfter() endAt(), endBefore(), or equalTo() must be a valid priority value (null, a number, or a string).")}else if(f(n.getIndex()instanceof ei||n.getIndex()===ti,"unknown index type."),e!=null&&typeof e=="object"||t!=null&&typeof t=="object")throw new Error("Query: First argument passed to startAt(), startAfter(), endAt(), endBefore(), or equalTo() cannot be an object.")}function mn(n){if(n.hasStart()&&n.hasEnd()&&n.hasLimit()&&!n.hasAnchoredLimit())throw new Error("Query: Can't combine startAt(), startAfter(), endAt(), endBefore(), and limit(). Use limitToFirst() or limitToLast() instead.")}class Q extends V{constructor(e,t){super(e,t,new Vt,!1)}get parent(){const e=dr(this._path);return e===null?null:new Q(this._repo,e)}get root(){let e=this;for(;e.parent!==null;)e=e.parent;return e}}class Se{constructor(e,t,i){this._node=e,this.ref=t,this._index=i}get priority(){return this._node.getPriority().val()}get key(){return this.ref.key}get size(){return this._node.numChildren()}child(e){const t=new E(e),i=Fe(this.ref,e);return new Se(this._node.getChild(t),i,T)}exists(){return!this._node.isEmpty()}exportVal(){return this._node.val(!0)}forEach(e){return this._node.isLeafNode()?!1:!!this._node.forEachChild(this._index,(i,s)=>e(new Se(s,Fe(this.ref,i),T)))}hasChild(e){const t=new E(e);return!this._node.getChild(t).isEmpty()}hasChildren(){return this._node.isLeafNode()?!1:!this._node.isEmpty()}toJSON(){return this.exportVal()}val(){return this._node.val()}}function wo(n,e){return n=U(n),n._checkNotDeleted("ref"),e!==void 0?Fe(n._root,e):n._root}function kd(n,e){n=U(n),n._checkNotDeleted("refFromURL");const t=Ui(e,n._repo.repoInfo_.nodeAdmin);so("refFromURL",t);const i=t.repoInfo;return!n._repo.repoInfo_.isCustomHost()&&i.host!==n._repo.repoInfo_.host&&te("refFromURL: Host name does not match the current database: (found "+i.host+" but expected "+n._repo.repoInfo_.host+")"),wo(n,t.path.toString())}function Fe(n,e){return n=U(n),y(n._path)===null?ad("child","path",e,!1):hn("child","path",e,!1),new Q(n._repo,k(n._path,e))}function Nd(n){return n=U(n),new bo(n._repo,n._path)}function Rd(n,e){n=U(n),ee("push",n._path),ie("push",e,n._path,!0);const t=lo(n._repo),i=Ad(t),s=Fe(n,i),r=Fe(n,i);let o;return e!=null?o=Hi(r,e).then(()=>r):o=Promise.resolve(r),s.then=o.then.bind(o),s.catch=o.then.bind(o,void 0),s}function Pd(n){return ee("remove",n._path),Hi(n,null)}function Hi(n,e){n=U(n),ee("set",n._path),ie("set",e,n._path,!1);const t=new z;return Mi(n._repo,n._path,e,null,t.wrapCallback(()=>{})),t.promise}function Dd(n,e){n=U(n),ee("setPriority",n._path),Di("setPriority",e);const t=new z;return Mi(n._repo,k(n._path,".priority"),e,null,t.wrapCallback(()=>{})),t.promise}function Od(n,e,t){if(ee("setWithPriority",n._path),ie("setWithPriority",e,n._path,!1),Di("setWithPriority",t),n.key===".length"||n.key===".keys")throw"setWithPriority failed: "+n.key+" is a read-only object.";const i=new z;return Mi(n._repo,n._path,e,t,i.wrapCallback(()=>{})),i.promise}function Md(n,e){io("update",e,n._path);const t=new z;return _d(n._repo,n._path,e,t.wrapCallback(()=>{})),t.promise}function Ld(n){n=U(n);const e=new $i(()=>{}),t=new At(e);return pd(n._repo,n,t).then(i=>new Se(i,new Q(n._repo,n._path),n._queryParams.getIndex()))}class At{constructor(e){this.callbackContext=e}respondsTo(e){return e==="value"}createEvent(e,t){const i=t._queryParams.getIndex();return new yo("value",this,new Se(e.snapshotNode,new Q(t._repo,t._path),i))}getEventRunner(e){return e.getEventType()==="cancel"?()=>this.callbackContext.onCancel(e.error):()=>this.callbackContext.onValue(e.snapshot,null)}createCancelEvent(e,t){return this.callbackContext.hasCancelCallback?new vo(this,e,t):null}matches(e){return e instanceof At?!e.callbackContext||!this.callbackContext?!0:e.callbackContext.matches(this.callbackContext):!1}hasAnyCallback(){return this.callbackContext!==null}}class gn{constructor(e,t){this.eventType=e,this.callbackContext=t}respondsTo(e){let t=e==="children_added"?"child_added":e;return t=t==="children_removed"?"child_removed":t,this.eventType===t}createCancelEvent(e,t){return this.callbackContext.hasCancelCallback?new vo(this,e,t):null}createEvent(e,t){f(e.childName!=null,"Child events should have a childName.");const i=Fe(new Q(t._repo,t._path),e.childName),s=t._queryParams.getIndex();return new yo(e.type,this,new Se(e.snapshotNode,i,s),e.prevName)}getEventRunner(e){return e.getEventType()==="cancel"?()=>this.callbackContext.onCancel(e.error):()=>this.callbackContext.onValue(e.snapshot,e.prevName)}matches(e){return e instanceof gn?this.eventType===e.eventType&&(!this.callbackContext||!e.callbackContext||this.callbackContext.matches(e.callbackContext)):!1}hasAnyCallback(){return!!this.callbackContext}}function kt(n,e,t,i,s){let r;if(typeof i=="object"&&(r=void 0,s=i),typeof i=="function"&&(r=i),s&&s.onlyOnce){const l=t,c=(d,h)=>{Li(n._repo,n,a),l(d,h)};c.userCallback=t.userCallback,c.context=t.context,t=c}const o=new $i(t,r||void 0),a=e==="value"?new At(o):new gn(e,o);return bd(n._repo,n,a),()=>Li(n._repo,n,a)}function Co(n,e,t,i){return kt(n,"value",e,t,i)}function Fd(n,e,t,i){return kt(n,"child_added",e,t,i)}function Bd(n,e,t,i){return kt(n,"child_changed",e,t,i)}function Wd(n,e,t,i){return kt(n,"child_moved",e,t,i)}function Ud(n,e,t,i){return kt(n,"child_removed",e,t,i)}function $d(n,e,t){let i=null;const s=t?new $i(t):null;e==="value"?i=new At(s):e&&(i=new gn(e,s)),Li(n._repo,n,i)}class Y{}class Eo extends Y{constructor(e,t){super(),this._value=e,this._key=t,this.type="endAt"}_apply(e){ie("endAt",this._value,e._path,!0);const t=si(e._queryParams,this._value,this._key);if(mn(t),Ee(t),e._queryParams.hasEnd())throw new Error("endAt: Starting point was already set (by another call to endAt, endBefore or equalTo).");return new V(e._repo,e._path,t,e._orderByCalled)}}function Hd(n,e){return xt("endAt","key",e),new Eo(n,e)}class Vd extends Y{constructor(e,t){super(),this._value=e,this._key=t,this.type="endBefore"}_apply(e){ie("endBefore",this._value,e._path,!1);const t=jc(e._queryParams,this._value,this._key);if(mn(t),Ee(t),e._queryParams.hasEnd())throw new Error("endBefore: Starting point was already set (by another call to endAt, endBefore or equalTo).");return new V(e._repo,e._path,t,e._orderByCalled)}}function zd(n,e){return xt("endBefore","key",e),new Vd(n,e)}class So extends Y{constructor(e,t){super(),this._value=e,this._key=t,this.type="startAt"}_apply(e){ie("startAt",this._value,e._path,!0);const t=ii(e._queryParams,this._value,this._key);if(mn(t),Ee(t),e._queryParams.hasStart())throw new Error("startAt: Starting point was already set (by another call to startAt, startBefore or equalTo).");return new V(e._repo,e._path,t,e._orderByCalled)}}function jd(n=null,e){return xt("startAt","key",e),new So(n,e)}class Gd extends Y{constructor(e,t){super(),this._value=e,this._key=t,this.type="startAfter"}_apply(e){ie("startAfter",this._value,e._path,!1);const t=zc(e._queryParams,this._value,this._key);if(mn(t),Ee(t),e._queryParams.hasStart())throw new Error("startAfter: Starting point was already set (by another call to startAt, startAfter, or equalTo).");return new V(e._repo,e._path,t,e._orderByCalled)}}function qd(n,e){return xt("startAfter","key",e),new Gd(n,e)}class Kd extends Y{constructor(e){super(),this._limit=e,this.type="limitToFirst"}_apply(e){if(e._queryParams.hasLimit())throw new Error("limitToFirst: Limit was already set (by another call to limitToFirst or limitToLast).");return new V(e._repo,e._path,Hc(e._queryParams,this._limit),e._orderByCalled)}}function Qd(n){if(typeof n!="number"||Math.floor(n)!==n||n<=0)throw new Error("limitToFirst: First argument must be a positive integer.");return new Kd(n)}class Yd extends Y{constructor(e){super(),this._limit=e,this.type="limitToLast"}_apply(e){if(e._queryParams.hasLimit())throw new Error("limitToLast: Limit was already set (by another call to limitToFirst or limitToLast).");return new V(e._repo,e._path,Vc(e._queryParams,this._limit),e._orderByCalled)}}function Jd(n){if(typeof n!="number"||Math.floor(n)!==n||n<=0)throw new Error("limitToLast: First argument must be a positive integer.");return new Yd(n)}class Xd extends Y{constructor(e){super(),this._path=e,this.type="orderByChild"}_apply(e){_n(e,"orderByChild");const t=new E(this._path);if(v(t))throw new Error("orderByChild: cannot pass in empty path. Use orderByValue() instead.");const i=new ei(t),s=zt(e._queryParams,i);return Ee(s),new V(e._repo,e._path,s,!0)}}function Zd(n){if(n==="$key")throw new Error('orderByChild: "$key" is invalid.  Use orderByKey() instead.');if(n==="$priority")throw new Error('orderByChild: "$priority" is invalid.  Use orderByPriority() instead.');if(n==="$value")throw new Error('orderByChild: "$value" is invalid.  Use orderByValue() instead.');return hn("orderByChild","path",n,!1),new Xd(n)}class eu extends Y{constructor(){super(...arguments),this.type="orderByKey"}_apply(e){_n(e,"orderByKey");const t=zt(e._queryParams,ne);return Ee(t),new V(e._repo,e._path,t,!0)}}function tu(){return new eu}class nu extends Y{constructor(){super(...arguments),this.type="orderByPriority"}_apply(e){_n(e,"orderByPriority");const t=zt(e._queryParams,T);return Ee(t),new V(e._repo,e._path,t,!0)}}function iu(){return new nu}class su extends Y{constructor(){super(...arguments),this.type="orderByValue"}_apply(e){_n(e,"orderByValue");const t=zt(e._queryParams,ti);return Ee(t),new V(e._repo,e._path,t,!0)}}function ru(){return new su}class ou extends Y{constructor(e,t){super(),this._value=e,this._key=t,this.type="equalTo"}_apply(e){if(ie("equalTo",this._value,e._path,!1),e._queryParams.hasStart())throw new Error("equalTo: Starting point was already set (by another call to startAt/startAfter or equalTo).");if(e._queryParams.hasEnd())throw new Error("equalTo: Ending point was already set (by another call to endAt/endBefore or equalTo).");return new Eo(this._value,this._key)._apply(new So(this._value,this._key)._apply(e))}}function au(n,e){return xt("equalTo","key",e),new ou(n,e)}function lu(n,...e){let t=U(n);for(const i of e)t=i._apply(t);return t}Dh(Q),Bh(Q);/**
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
 */const cu="FIREBASE_DATABASE_EMULATOR_HOST",Vi={};let Io=!1;function hu(n,e,t,i){const s=e.lastIndexOf(":"),r=e.substring(0,s),o=os(r);n.repoInfo_=new qs(e,o,n.repoInfo_.namespace,n.repoInfo_.webSocketOnly,n.repoInfo_.nodeAdmin,n.repoInfo_.persistenceKey,n.repoInfo_.includeNamespaceInQueryParams,!0,t),i&&(n.authTokenProvider_=i)}function zi(n,e,t,i,s){let r=i||n.options.databaseURL;r===void 0&&(n.options.projectId||te("Can't determine Firebase Database URL. Be sure to include  a Project ID when calling firebase.initializeApp()."),D("Using default host for project ",n.options.projectId),r=`${n.options.projectId}-default-rtdb.firebaseio.com`);let o=Ui(r,s),a=o.repoInfo,l,c;typeof process<"u"&&process.env&&(c=process.env[cu]),c?(l=!0,r=`http://${c}?ns=${a.namespace}`,o=Ui(r,s),a=o.repoInfo):l=!o.repoInfo.secure;const d=s&&l?new ze(ze.OWNER):new ql(n.name,n.options,e);so("Invalid Firebase Database URL",o),v(o.path)||te("Database URL must point to the root of a Firebase Database (not including a child path).");const h=uu(a,n,d,new Gl(n,t));return new xo(h,n)}function du(n,e){const t=Vi[e];(!t||t[n.key]!==n)&&te(`Database ${e}(${n.repoInfo_}) has already been deleted.`),fo(n),delete t[n.key]}function uu(n,e,t,i){let s=Vi[e.name];s||(s={},Vi[e.name]=s);let r=s[n.toURLString()];return r&&te("Database initialized multiple times. Please make sure the format of the database URL matches with each database() call."),r=new dd(n,Io,t,i),s[n.toURLString()]=r,r}function fu(n){Io=n}class xo{constructor(e,t){this._repoInternal=e,this.app=t,this.type="database",this._instanceStarted=!1}get _repo(){return this._instanceStarted||(ud(this._repoInternal,this.app.options.appId,this.app.options.databaseAuthVariableOverride),this._instanceStarted=!0),this._repoInternal}get _root(){return this._rootInternal||(this._rootInternal=new Q(this._repo,w())),this._rootInternal}_delete(){return this._rootInternal!==null&&(du(this._repo,this.app.name),this._repoInternal=null,this._rootInternal=null),Promise.resolve()}_checkNotDeleted(e){this._rootInternal===null&&te("Cannot call "+e+" on a deleted database.")}}function To(){je.IS_TRANSPORT_INITIALIZED&&B("Transport has already been initialized. Please call this function before calling ref or setting up a listener")}function pu(){To(),_e.forceDisallow()}function _u(){To(),G.forceDisallow(),_e.forceAllow()}function mu(n=bs(),e){const t=On(n,"database").getImmediate({identifier:e});if(!t._instanceStarted){const i=na("database");i&&Ao(t,...i)}return t}function Ao(n,e,t,i={}){n=U(n),n._checkNotDeleted("useEmulator");const s=`${e}:${t}`,r=n._repoInternal;if(n._instanceStarted){if(s===n._repoInternal.repoInfo_.host&&Dt(i,r.repoInfo_.emulatorOptions))return;te("connectDatabaseEmulator() cannot initialize or alter the emulator configuration after the database instance has started.")}let o;if(r.repoInfo_.nodeAdmin)i.mockUserToken&&te('mockUserToken is not supported by the Admin SDK. For client access with mock users, please use the "firebase" package instead of "firebase-admin".'),o=new ze(ze.OWNER);else if(i.mockUserToken){const a=typeof i.mockUserToken=="string"?i.mockUserToken:ia(i.mockUserToken,n.app.options.projectId);o=new ze(a)}os(e)&&ga(e),hu(r,s,i,o)}function gu(n){n=U(n),n._checkNotDeleted("goOffline"),fo(n._repo)}function yu(n){n=U(n),n._checkNotDeleted("goOnline"),wd(n._repo)}function vu(n,e){Os(n,e)}/**
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
 */function bu(n){Fn(ys),it(new Te("database",(e,{instanceIdentifier:t})=>{const i=e.getProvider("app").getImmediate(),s=e.getProvider("auth-internal"),r=e.getProvider("app-check-internal");return zi(i,s,r,t)},"PUBLIC").setMultipleInstances(!0)),fe(Ts,As,n),fe(Ts,As,"esm2020")}/**
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
 */const wu={".sv":"timestamp"};function Cu(){return wu}function Eu(n){return{".sv":{increment:n}}}/**
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
 */class ko{constructor(e,t){this.committed=e,this.snapshot=t}toJSON(){return{committed:this.committed,snapshot:this.snapshot.toJSON()}}}function Su(n,e,t){if(n=U(n),ee("Reference.transaction",n._path),n.key===".length"||n.key===".keys")throw"Reference.transaction failed: "+n.key+" is a read-only object.";const i=(t==null?void 0:t.applyLocally)??!0,s=new z,r=(a,l,c)=>{let d=null;a?s.reject(a):(d=new Se(c,new Q(n._repo,n._path),T),s.resolve(new ko(l,d)))},o=Co(n,()=>{});return Cd(n._repo,n._path,e,r,o,i),s.promise}/**
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
 */K.prototype.simpleListen=function(n,e){this.sendRequest("q",{p:n},e)},K.prototype.echo=function(n,e){this.sendRequest("echo",{d:n},e)};const Iu=function(n){const e=K.prototype.put;return K.prototype.put=function(t,i,s,r){r!==void 0&&(r=n()),e.call(this,t,i,s,r)},function(){K.prototype.put=e}},xu=function(n){fu(n)};/**
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
 */function Tu({app:n,url:e,version:t,customAuthImpl:i,customAppCheckImpl:s,nodeAdmin:r=!1}){Fn(t);const o=new Sn("database-standalone"),a=new En("auth-internal",o);let l;return s&&(l=new En("app-check-internal",o),l.setComponent(new Te("app-check-internal",()=>s,"PRIVATE"))),a.setComponent(new Te("auth-internal",()=>i,"PRIVATE")),zi(n,a,l,e,r)}bu();const Au=Object.freeze(Object.defineProperty({__proto__:null,DataSnapshot:Se,Database:xo,OnDisconnect:bo,QueryConstraint:Y,TransactionResult:ko,_QueryImpl:V,_QueryParams:Vt,_ReferenceImpl:Q,_TEST_ACCESS_forceRestClient:xu,_TEST_ACCESS_hijackHash:Iu,_initStandalone:Tu,_repoManagerDatabaseFromApp:zi,_setSDKVersion:Fn,_validatePathString:hn,_validateWritablePath:ee,child:Fe,connectDatabaseEmulator:Ao,enableLogging:vu,endAt:Hd,endBefore:zd,equalTo:au,forceLongPolling:_u,forceWebSockets:pu,get:Ld,getDatabase:mu,goOffline:gu,goOnline:yu,increment:Eu,limitToFirst:Qd,limitToLast:Jd,off:$d,onChildAdded:Fd,onChildChanged:Bd,onChildMoved:Wd,onChildRemoved:Ud,onDisconnect:Nd,onValue:Co,orderByChild:Zd,orderByKey:tu,orderByPriority:iu,orderByValue:ru,push:Rd,query:lu,ref:wo,refFromURL:kd,remove:Pd,runTransaction:Su,serverTimestamp:Cu,set:Hi,setPriority:Dd,setWithPriority:Od,startAfter:qd,startAt:jd,update:Md},Symbol.toStringTag,{value:"Module"}));se.ApriloWidget=vn,se.default=Qi,Object.defineProperties(se,{__esModule:{value:!0},[Symbol.toStringTag]:{value:"Module"}})})(this.ApriloChat=this.ApriloChat||{});
