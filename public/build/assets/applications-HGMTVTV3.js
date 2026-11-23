(()=>{var r=sessionStorage.getItem("api-gateway-token");r||window.location.replace("/login");var n=document.querySelector("#status-banner"),p=document.querySelector("#applications-body"),o=document.querySelector("#refresh-button"),A=document.querySelector("#logout-button");if(r&&n&&p&&o&&A){let i=[],s=t=>String(t??"").replaceAll("&","&amp;").replaceAll("<","&lt;").replaceAll(">","&gt;").replaceAll('"',"&quot;").replaceAll("'","&#39;"),c=(t,a=!1)=>{n.textContent=t,n.classList.toggle("status-banner--success",a),n.hidden=!t},y=(t,a)=>Array.isArray(t)&&t.length>0?i.filter(e=>t.includes(e.key)).map(e=>e.name):i.filter(e=>(e.requiredAbilities??[]).every(l=>a.includes(l))).map(e=>e.name),u=(t,a)=>t.map(e=>`<span class="chip ${a}">${s(e)}</span>`).join(""),h=t=>{p.innerHTML=`<tr><td colspan="6" class="empty-state">${s(t)}</td></tr>`},w=t=>{if(!Array.isArray(t)||t.length===0){h("No applications were found for this account.");return}p.innerHTML=t.map(a=>{let e=a.attributes??{},l=e.name||`Application #${a.id}`,b=e.description||"No description provided.",d=Array.isArray(e.allowedAbilities)?e.allowedAbilities:[],S=Array.isArray(e.assignedServices)?e.assignedServices:[],v=y(S,d),$=!!e.status?'<span class="chip">Active</span>':'<span class="chip chip--inactive">Inactive</span>';return`
            <tr>
                <td>
                    <div class="application-name">${s(l)}</div>
                    <div class="muted">ID: ${s(a.id)}</div>
                </td>
                <td>${s(b)}</td>
                <td>${$}</td>
                <td>
                    <div class="chip-list">
                        ${d.length>0?u(d,""):'<span class="muted">None</span>'}
                    </div>
                </td>
                <td>
                    <div class="chip-list">
                        ${v.length>0?u(v,"chip--service"):'<span class="muted">No API services mapped</span>'}
                    </div>
                </td>
                <td>
                    <a class="row-action-link" href="/applications-access/${encodeURIComponent(a.id)}/edit">Edit</a>
                </td>
            </tr>
        `}).join("")},g=t=>{o.disabled=t,o.textContent=t?"Refreshing\u2026":"Refresh"},f=async()=>{let t=await fetch("/api/v1/service-catalog",{method:"GET",headers:{Accept:"application/json",Authorization:`Bearer ${r}`,"X-Requested-With":"XMLHttpRequest"}}),a=await t.json().catch(()=>({}));if(!t.ok){if(t.status===401){sessionStorage.removeItem("api-gateway-token"),window.location.replace("/login");return}throw new Error(a.message||"Unable to load service catalog.")}i=Array.isArray(a.data)?a.data:[]},m=async()=>{g(!0),c("");try{i.length===0&&await f();let t=await fetch("/api/v1/applications",{method:"GET",headers:{Accept:"application/json",Authorization:`Bearer ${r}`,"X-Requested-With":"XMLHttpRequest"}}),a=await t.json().catch(()=>({}));if(!t.ok){if(t.status===401){sessionStorage.removeItem("api-gateway-token"),window.location.replace("/login");return}throw new Error(a.message||"Unable to load applications.")}let e=Array.isArray(a.data)?a.data:[];w(e),c(`Loaded ${e.length} application${e.length===1?"":"s"}.`,!0)}catch(t){h("Unable to load application access right now."),c(t instanceof Error?t.message:"Unable to load applications.")}finally{g(!1)}};o.addEventListener("click",m),A.addEventListener("click",()=>{sessionStorage.removeItem("api-gateway-token"),window.location.assign("/login")}),m()}})();
