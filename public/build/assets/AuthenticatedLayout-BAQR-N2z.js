import{_ as k,r as f,a1 as b,f as i,o as n,b as s,a as o,w as a,u as l,P as d,n as r,g as w,t as m,a2 as C,k as $,W as z}from"./app-lBFWFbO3.js";const L={class:"app-container"},S={class:"white-box",style:{"border-color":"white"}},V={xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",width:"24",height:"24"},P={key:0,d:"M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z",fill:"currentColor"},B={key:1,d:"M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z",fill:"currentColor"},H={class:"sidebar-menu"},M={class:"menu-content"},A={class:"menu-icon"},j={key:0,src:"/assets/images/dashboard-b.png",class:"dashboard-icon",style:{height:"24px"}},F={key:1,src:"/assets/images/dashboard-w.png",class:"dashboard-icon",style:{height:"24px"}},I={class:"menu-content"},N={class:"menu-icon"},T={key:0,src:"/assets/images/tracking-b.png",class:"order-icon",style:{height:"24px"}},O={key:1,src:"/assets/images/tracking-w.png",class:"order-icon",style:{height:"24px"}},D={class:"menu-content"},E={class:"menu-icon"},W={key:0,src:"/assets/images/transfer-b.png",class:"transfer-icon",style:{height:"24px"}},q={key:1,src:"/assets/images/transfer-w.png",class:"transfer-icon",style:{height:"24px"}},U={class:"menu-content"},G={class:"menu-icon"},J={key:0,src:"/assets/images/product-b.png",class:"product-icon",style:{height:"24px"}},K={key:1,src:"/assets/images/product-w.png",class:"product-icon",style:{height:"24px"}},Q={class:"menu-content"},R={class:"menu-icon"},X={key:0,src:"/assets/images/po-b.png",class:"order-icon",style:{height:"24px"}},Y={key:1,src:"/assets/images/po-w.png",class:"order-icon",style:{height:"24px"}},Z={class:"menu-content"},ss={class:"menu-icon"},es={key:0,src:"/assets/images/inventory-b.png",class:"inventory-icon",style:{height:"24px"}},ts={key:1,src:"/assets/images/inventory-w.png",class:"inventory-icon",style:{height:"24px"}},is={class:"menu-content"},ns={class:"menu-icon"},os={key:0,src:"/assets/images/expire-b.png",class:"expired-icon",style:{height:"24px"}},rs={key:1,src:"/assets/images/expire-w.png",class:"expired-icon",style:{height:"24px"}},as={class:"menu-content"},ls={class:"menu-icon"},ds={key:0,src:"/assets/images/supplier-b.png",class:"supplies-icon",style:{height:"24px"}},cs={key:1,src:"/assets/images/supplier-w.png",class:"supplies-icon",style:{height:"24px"}},us={class:"menu-content"},gs={class:"menu-icon"},ps={key:0,src:"/assets/images/facility-b.png",class:"facility-icon",style:{height:"24px"}},ms={key:1,src:"/assets/images/facility-w.png",class:"facility-icon",style:{height:"24px"}},hs={class:"menu-content"},vs={class:"menu-icon"},fs={key:0,src:"/assets/images/setting-b.png",class:"setting-icon",style:{height:"24px"}},ys={key:1,src:"/assets/images/setting-w.png",class:"setting-icon",style:{height:"24px"}},xs={class:"top-nav"},_s={class:"inventory-banner"},ks={class:"flex justify-between"},bs={class:"flex flex-col"},ws={xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",width:"24",height:"24"},Cs={key:0,d:"M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z",fill:"currentColor"},$s={key:1,d:"M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z",fill:"currentColor"},zs={class:"inventory-text"},Ls={class:"text-black text-lg"},Ss={key:0},Vs=["src"],Ps={class:"user-section"},Bs={class:"flex flex-row"},Hs={class:"user-info"},Ms={class:"user-details"},As={class:"user-name"},js={class:"flex flex-col relative pb-16"},Fs={class:"flex-1"},Is={__name:"AuthenticatedLayout",props:{title:{type:String,required:!0},description:{type:String,default:""},img:{type:String,default:"/assets/images/head_web.gif"}},setup(p){const g=f(!0),y=f("dashboard"),h=()=>{g.value=!g.value},c=t=>{y.value=t},x=()=>{z.post(route("logout"))};return(t,e)=>{var v;const _=b("ToastContainer");return n(),i("div",L,[s("div",{class:r(["sidebar",{"sidebar-open":g.value}])},[s("div",S,[o(l(d),{href:t.route("dashboard"),class:"logo-container"},{default:a(()=>e[10]||(e[10]=[s("img",{src:"/assets/images/moh.png",class:"moh-logo",style:{height:"50px"}},null,-1),s("img",{src:"/assets/images/psi.jpg",class:"psi-logo",style:{height:"50px"}},null,-1)])),_:1},8,["href"])]),s("button",{onClick:h,class:"sidebar-toggle"},[(n(),i("svg",V,[g.value?(n(),i("path",B)):(n(),i("path",P))]))]),s("div",H,[o(l(d),{href:t.route("dashboard"),class:r(["menu-item",{active:t.route().current("dashboard")}]),style:{"margin-top":"2rem"},onClick:e[0]||(e[0]=u=>c("dashboard"))},{default:a(()=>[s("div",M,[s("div",A,[t.route().current("dashboard")?(n(),i("img",j)):(n(),i("img",F))]),e[11]||(e[11]=s("span",{class:"menu-text"},"Dashboard",-1))])]),_:1},8,["href","class"]),o(l(d),{href:t.route("orders.index"),class:r(["menu-item",{active:t.route().current("orders.*")}]),onClick:e[1]||(e[1]=u=>c("orders"))},{default:a(()=>[s("div",I,[s("div",N,[t.route().current("orders.*")?(n(),i("img",T)):(n(),i("img",O))]),e[12]||(e[12]=s("span",{class:"menu-text"},"Orders",-1))])]),_:1},8,["href","class"]),o(l(d),{href:t.route("transfers.index"),class:r(["menu-item",{active:t.route().current("transfers.*")}]),onClick:e[2]||(e[2]=u=>c("transfers"))},{default:a(()=>[s("div",D,[s("div",E,[t.route().current("transfers.*")?(n(),i("img",W)):(n(),i("img",q))]),e[13]||(e[13]=s("span",{class:"menu-text"},"Transfers",-1))])]),_:1},8,["href","class"]),o(l(d),{href:t.route("products.index"),class:r(["menu-item",{active:t.route().current("products.*")}]),onClick:e[3]||(e[3]=u=>c("products"))},{default:a(()=>[s("div",U,[s("div",G,[t.route().current("products.*")?(n(),i("img",J)):(n(),i("img",K))]),e[14]||(e[14]=s("span",{class:"menu-text"},"Product List",-1))])]),_:1},8,["href","class"]),o(l(d),{href:t.route("purchase-orders.index"),class:r(["menu-item",{active:t.route().current("purchase-orders.*")}]),onClick:e[4]||(e[4]=u=>c("purchase-orders"))},{default:a(()=>[s("div",Q,[s("div",R,[t.route().current("purchase-orders.*")?(n(),i("img",X)):(n(),i("img",Y))]),e[15]||(e[15]=s("span",{class:"menu-text"},"Purchase Orders",-1))])]),_:1},8,["href","class"]),o(l(d),{href:t.route("inventories.index"),class:r(["menu-item",{active:t.route().current("inventories.*")}]),onClick:e[5]||(e[5]=u=>c("inventories"))},{default:a(()=>[s("div",Z,[s("div",ss,[t.route().current("inventories.*")?(n(),i("img",es)):(n(),i("img",ts))]),e[16]||(e[16]=s("span",{class:"menu-text"},"Inventory",-1))])]),_:1},8,["href","class"]),o(l(d),{href:t.route("expired.index"),class:r(["menu-item",{active:t.route().current("expired.*")}]),onClick:e[6]||(e[6]=u=>c("expired"))},{default:a(()=>[s("div",is,[s("div",ns,[t.route().current("expired.*")?(n(),i("img",os)):(n(),i("img",rs))]),e[17]||(e[17]=s("span",{class:"menu-text"},"Expires",-1))])]),_:1},8,["href","class"]),o(l(d),{href:t.route("supplies.index"),class:r(["menu-item",{active:t.route().current("supplies.*")}]),onClick:e[7]||(e[7]=u=>c("supplies"))},{default:a(()=>[s("div",as,[s("div",ls,[t.route().current("supplies.*")?(n(),i("img",ds)):(n(),i("img",cs))]),e[18]||(e[18]=s("span",{class:"menu-text"},"Supplies",-1))])]),_:1},8,["href","class"]),o(l(d),{href:t.route("facilities.index"),class:r(["menu-item",{active:t.route().current("facilities.*")}]),onClick:e[8]||(e[8]=u=>c("facilities"))},{default:a(()=>[s("div",us,[s("div",gs,[t.route().current("facilities.*")?(n(),i("img",ps)):(n(),i("img",ms))]),e[19]||(e[19]=s("span",{class:"menu-text"},"Facilities",-1))])]),_:1},8,["href","class"]),o(l(d),{href:t.route("settings.index"),class:r(["menu-item",{active:t.route().current("settings.*")}]),onClick:e[9]||(e[9]=u=>c("settings"))},{default:a(()=>[s("div",hs,[s("div",vs,[t.route().current("settings.*")?(n(),i("img",fs)):(n(),i("img",ys))]),e[20]||(e[20]=s("span",{class:"menu-text"},"Settings",-1))])]),_:1},8,["href","class"])])],2),s("div",{class:r(["main-content",{"main-content-expanded":!g.value}])},[s("div",xs,[s("div",_s,[s("div",ks,[s("div",bs,[s("button",{onClick:h,class:"back-button"},[(n(),i("svg",ws,[g.value?(n(),i("path",Cs)):(n(),i("path",$s))]))]),s("div",zs,[s("h1",null,m(p.title),1),s("h3",Ls,'"'+m(p.description)+'"',1)])]),p.img?(n(),i("div",Ss,[s("img",{src:p.img,alt:"Inventory illustration",class:"svg-image"},null,8,Vs)])):w("",!0)]),s("div",Ps,[s("div",Bs,[e[24]||(e[24]=s("div",{class:"notification-icon"},[s("svg",{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",width:"24",height:"24"},[s("path",{d:"M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.63-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.64 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2zm-2 1H8v-6c0-2.48 1.51-4.5 4-4.5s4 2.02 4 4.5v6z",fill:"#FFF"})])],-1)),s("div",Hs,[e[22]||(e[22]=s("div",{class:"user-avatar"},[s("span",null,"A")],-1)),s("div",Ms,[e[21]||(e[21]=s("span",{class:"user-role"},"Pharmaceutical Manager",-1)),s("span",As,m((v=t.$page.props.auth.user)==null?void 0:v.name),1)])]),s("button",{class:"logout-button",onClick:x},e[23]||(e[23]=[s("svg",{xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 24 24",width:"24",height:"24",fill:"none",stroke:"currentColor","stroke-width":"2","stroke-linecap":"round","stroke-linejoin":"round"},[s("path",{d:"M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"}),s("polyline",{points:"16 17 21 12 16 7"}),s("line",{x1:"21",y1:"12",x2:"9",y2:"12"})],-1)]))]),e[25]||(e[25]=s("img",{src:"/assets/images/head_web.gif",alt:"Inventory illustration",class:"svg-image"},null,-1))])])]),s("main",js,[s("div",Fs,[$(t.$slots,"default",{},void 0,!0)]),e[26]||(e[26]=C('<div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200" data-v-9d5fdd32><div class="container mx-auto py-2" data-v-9d5fdd32><div class="flex justify-center items-center gap-4" data-v-9d5fdd32><img src="/assets/images/vista.png" alt="Vista" class="w-[80px]" data-v-9d5fdd32><span class="flex items-center text-gray-400" data-v-9d5fdd32>|</span><span class="flex items-center text-gray-600" data-v-9d5fdd32>Copyright 2025 Vista. All rights reserved.</span><span class="flex items-center text-gray-400" data-v-9d5fdd32>|</span><span class="flex items-center text-gray-600 hover:text-gray-800 cursor-pointer" data-v-9d5fdd32>Terms of Use</span><span class="flex items-center text-gray-400" data-v-9d5fdd32>|</span><span class="flex items-center text-gray-600 hover:text-gray-800 cursor-pointer" data-v-9d5fdd32>Privacy</span></div></div></div>',1))])],2),o(_)])}}},Ts=k(Is,[["__scopeId","data-v-9d5fdd32"]]);export{Ts as A};
