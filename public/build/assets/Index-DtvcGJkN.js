import{l as oe,r as c,s as le,W as ae,f as d,o as u,a as l,u as n,m as re,w as i,b as e,i as g,p as h,x as D,e as C,g as _,t as a,F as P,q as N,P as ne,n as ie,d as de,B as Q}from"./app-lBFWFbO3.js";import{A as ue}from"./AuthenticatedLayout-BAQR-N2z.js";import{_ as pe}from"./Pagination-B1ZOkbJe.js";import"./vue-multiselect-MGhEWrU-.js";import"./lodash-DBpAJb-p.js";import"./sweetalert2.esm.all-B0Dix5B2.js";import{S as R,Y as W,h as z,G as Y,V as J}from"./transition-CVw4BkCX.js";const ce={class:"bg-white overflow-hidden sm:rounded-lg"},me={class:"p-6 bg-white border-b border-gray-200"},fe={class:"flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6"},ge={class:"flex flex-col md:flex-row gap-3 md:items-center flex-grow"},ve={class:"w-full md:w-auto flex-grow relative"},xe={class:"flex gap-2"},ye={class:"flex flex-col"},be={class:"overflow-x-auto"},we={class:"inline-block min-w-full align-middle"},he={class:"overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg"},_e={class:"sticky top-0 z-10 bg-white"},ke={class:"min-w-full divide-y divide-gray-300"},Ce={class:"divide-y divide-gray-200 bg-white"},Se={key:0},Me={colspan:"6",class:"px-6 py-24 text-center"},Oe={class:"flex flex-col items-center"},Ve={class:"mt-2 text-sm text-gray-500"},Pe={key:0,class:"mt-4"},ze={class:"whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"},je={class:"whitespace-nowrap px-3 py-4 text-sm text-gray-500"},Ue={class:"whitespace-nowrap px-3 py-4 text-sm text-gray-500"},Ae={class:"whitespace-nowrap px-3 py-4 text-sm text-gray-500"},De={class:"whitespace-nowrap px-3 py-4 text-sm"},Ne={class:"relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6"},Be={class:"flex items-center gap-2"},Ie=["onClick"],Fe=["onClick"],Te=["onClick"],qe={class:"mt-4"},He={class:"fixed inset-0 overflow-y-auto"},Ee={class:"flex min-h-full items-center justify-center p-4 text-center sm:items-center sm:p-0"},Le={class:"grid grid-cols-3 gap-4"},$e=["value"],Ge={class:"mt-6 flex justify-end gap-3"},Qe=["disabled"],Re=["disabled"],We={class:"fixed inset-0 z-[60] w-screen overflow-y-auto"},Ye={class:"flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"},Je={class:"sm:flex sm:items-start"},Ke={class:"mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full"},Xe={key:0,class:"text-sm text-gray-500 ml-2"},Ze={class:"mt-4"},et={class:"overflow-x-auto"},tt={key:0,class:"min-w-full divide-y divide-gray-200"},st={class:"divide-y divide-gray-200 bg-white"},ot={class:"whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900"},lt={class:"whitespace-nowrap px-3 py-4 text-sm text-gray-500"},at={class:"whitespace-nowrap px-3 py-4 text-sm text-gray-500"},rt={class:"whitespace-nowrap px-3 py-4 text-sm text-gray-500"},nt={class:"whitespace-nowrap px-3 py-4 text-sm text-gray-500"},it={key:0},dt={key:0,class:"bg-gray-50"},ut={class:"whitespace-nowrap px-3 py-3.5 text-sm font-semibold text-gray-900"},yt={__name:"Index",props:{purchase_orders:Object,suppliers:Array,products:Array,filters:Object},setup(K){var q,H,E,L,$;const k=oe(),m=K,S=c(!1);c(!1);const j=c(!1),f=c(!1),B=c(null),p=c(null),X=s=>{p.value=s,j.value=!0},U=()=>{S.value=!1,r.value={id:null,po_number:"",supplier_id:"",po_date:new Date().toISOString().split("T")[0],total_amount:0,notes:"",status:"pending"},B.value=null},I=s=>new Intl.NumberFormat("en-US",{minimumFractionDigits:2,maximumFractionDigits:2}).format(s),A=()=>{j.value=!1,p.value=null},Z=async()=>{f.value=!0,await Q.post(route("purchase-orders.store"),r.value).then(s=>{f.value=!1,k.success(s.data),U(),O()}).catch(s=>{console.log(s),f.value=!1,k.error(s.response.data)})},ee=async()=>{if(p.value){f.value=!0;try{const s=await Q.delete(route("purchase-orders.destroy",p.value.id));s.data.success?(k.success(s.data||"Purchase order deleted successfully"),O()):k.error(s.data||"An error occurred")}catch(s){k.error("An error occurred while deleting the purchase order"),console.error("Error:",s)}finally{f.value=!1}}},M=s=>new Intl.NumberFormat("en-US",{style:"currency",currency:"USD"}).format(s),te=s=>new Intl.DateTimeFormat("en-US",{year:"numeric",month:"2-digit",day:"2-digit"}).format(new Date(s)),r=c({id:null,po_number:"",supplier_id:"",po_date:new Date().toISOString().split("T")[0],total_amount:0,notes:"",status:"pending",items:[]}),v=c(((q=m.filters)==null?void 0:q.search)||""),x=c(((H=m.filters)==null?void 0:H.status)||""),y=c(((E=m.filters)==null?void 0:E.start_date)||""),b=c(((L=m.filters)==null?void 0:L.end_date)||""),w=c((($=m.filters)==null?void 0:$.per_page)||10);le([()=>v.value,()=>x.value,()=>y.value,()=>b.value,()=>w.value],()=>{O()});const F=()=>{v.value="",x.value="",y.value="",b.value="",w.value=10,O()},O=()=>{const s={};v.value&&(s.search=v.value),x.value&&(s.status=x.value),y.value&&(s.start_date=y.value),b.value&&(s.end_date=b.value),w.value&&(s.per_page=w.value),ae.get(route("purchase-orders.index"),s,{preserveState:!0,preserveScroll:!0,only:["purchase_orders","suppliers","products"]})},T=(s=null)=>{var t;B.value=null,s?r.value={id:s.id,po_number:s.po_number,supplier_id:((t=s.supplier)==null?void 0:t.id)||"",po_date:s.po_date,total_amount:s.total_amount,status:s.status,notes:s.notes}:r.value={id:null,po_number:"",supplier_id:"",po_date:new Date().toISOString().split("T")[0],total_amount:0,status:"pending",notes:""},S.value=!0};function se(s){var t;r.value={id:s.id,po_number:s.po_number,supplier_id:((t=s.supplier)==null?void 0:t.id)||"",po_date:s.po_date,total_amount:s.total_amount,status:s.status,notes:s.notes},S.value=!0}return(s,t)=>(u(),d(P,null,[l(n(re),{title:"Purchase Orders"}),l(ue,null,{default:i(()=>[e("div",ce,[e("div",me,[e("div",fe,[e("div",ge,[e("div",ve,[t[9]||(t[9]=e("div",{class:"absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"},[e("svg",{class:"h-5 w-5 text-gray-400",xmlns:"http://www.w3.org/2000/svg",viewBox:"0 0 20 20",fill:"currentColor","aria-hidden":"true"},[e("path",{"fill-rule":"evenodd",d:"M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z","clip-rule":"evenodd"})])],-1)),g(e("input",{"onUpdate:modelValue":t[0]||(t[0]=o=>v.value=o),type:"text",placeholder:"Search by PO number, supplier...",class:"w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"},null,512),[[h,v.value]])]),g(e("select",{"onUpdate:modelValue":t[1]||(t[1]=o=>x.value=o),class:"w-full md:w-40 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"},t[10]||(t[10]=[e("option",{value:""},"All Status",-1),e("option",{value:"draft"},"Draft",-1),e("option",{value:"pending"},"Pending",-1),e("option",{value:"approved"},"Approved",-1),e("option",{value:"rejected"},"Rejected",-1),e("option",{value:"completed"},"Completed",-1)]),512),[[D,x.value]]),e("div",xe,[g(e("input",{type:"date","onUpdate:modelValue":t[2]||(t[2]=o=>y.value=o),class:"w-full md:w-40 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"},null,512),[[h,y.value]]),g(e("input",{type:"date","onUpdate:modelValue":t[3]||(t[3]=o=>b.value=o),class:"w-full md:w-40 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"},null,512),[[h,b.value]])]),g(e("select",{"onUpdate:modelValue":t[4]||(t[4]=o=>w.value=o),class:"w-full md:w-32 pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 transition duration-150"},t[11]||(t[11]=[e("option",{value:"5"},"5 / page",-1),e("option",{value:"10"},"10 / page",-1),e("option",{value:"25"},"25 / page",-1),e("option",{value:"50"},"50 / page",-1),e("option",{value:"100"},"100 / page",-1)]),512),[[D,w.value]]),e("button",{onClick:F,class:"inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"},t[12]||(t[12]=[e("span",{class:"flex items-center"},[e("svg",{class:"w-4 h-4 mr-2",xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"})]),C(" Reset ")],-1)]))]),e("div",{class:"mt-4 sm:mt-0 sm:ml-16 sm:flex-none space-x-4"},[e("button",{type:"button",onClick:T,class:"inline-flex items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:w-auto"}," Add Purchase Order ")])]),e("div",ye,[e("div",be,[e("div",we,[e("div",he,[e("div",_e,[e("table",ke,[t[19]||(t[19]=e("thead",{class:"bg-gray-50"},[e("tr",null,[e("th",{scope:"col",class:"py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"}," PO Number"),e("th",{scope:"col",class:"px-3 py-3.5 text-left text-sm font-semibold text-gray-900"}," Supplier"),e("th",{scope:"col",class:"px-3 py-3.5 text-left text-sm font-semibold text-gray-900"}," Date"),e("th",{scope:"col",class:"px-3 py-3.5 text-left text-sm font-semibold text-gray-900"}," Total Amount"),e("th",{scope:"col",class:"px-3 py-3.5 text-left text-sm font-semibold text-gray-900"}," Status"),e("th",{scope:"col",class:"relative py-3.5 pl-3 pr-4 sm:pr-6"},[e("span",{class:"sr-only"},"Actions")])])],-1)),e("tbody",Ce,[!m.purchase_orders.data||m.purchase_orders.data.length===0?(u(),d("tr",Se,[e("td",Me,[e("div",Oe,[t[14]||(t[14]=e("div",{class:"rounded-full bg-gray-100 p-3"},[e("svg",{class:"w-12 h-12 text-gray-400",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V7a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"})])],-1)),t[15]||(t[15]=e("h3",{class:"mt-4 text-base font-medium text-gray-900"},"No Purchase Orders Found",-1)),e("p",Ve,a(v.value||x.value||y.value||b.value?"No results match your search criteria.":"Get started by creating your first purchase order."),1),e("div",{class:"mt-6"},[e("button",{type:"button",onClick:T,class:"inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"},t[13]||(t[13]=[e("svg",{class:"w-5 h-5 mr-2",xmlns:"http://www.w3.org/2000/svg",fill:"none",viewBox:"0 0 24 24",stroke:"currentColor"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round","stroke-width":"2",d:"M12 6v6m0 0v6m0-6h6m-6 0H6"})],-1),C(" Create Purchase Order ")]))]),v.value||x.value||y.value||b.value?(u(),d("div",Pe,[e("button",{type:"button",onClick:F,class:"text-sm text-primary-600 hover:text-primary-500"}," Clear all filters ")])):_("",!0)])])])):_("",!0),(u(!0),d(P,null,N(m.purchase_orders.data,o=>{var V;return u(),d("tr",{key:o.id,class:"hover:bg-gray-50"},[e("td",ze,[l(n(ne),{href:s.route("purchase-orders.packing-list",o.id),class:"text-indigo-600 hover:text-indigo-900"},{default:i(()=>[C(a(o.po_number),1)]),_:2},1032,["href"])]),e("td",je,a((V=o.supplier)==null?void 0:V.name),1),e("td",Ue,a(te(o.po_date)),1),e("td",Ae,a(M(o.total_amount)),1),e("td",De,[e("span",{class:ie([{"bg-green-100 text-green-800":o.status==="completed","bg-yellow-100 text-yellow-800":o.status==="pending","bg-gray-100 text-gray-800":o.status==="draft"},"inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"])},a(o.status),3)]),e("td",Ne,[e("div",Be,[e("button",{onClick:G=>se(o),class:"text-indigo-600 hover:text-indigo-900"},t[16]||(t[16]=[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",viewBox:"0 0 20 20",fill:"currentColor"},[e("path",{d:"M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"})],-1)]),8,Ie),e("button",{onClick:G=>X(o),class:"text-blue-600 hover:text-blue-900"},t[17]||(t[17]=[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",viewBox:"0 0 20 20",fill:"currentColor"},[e("path",{d:"M10 12a2 2 0 100-4 2 2 0 000 4z"}),e("path",{"fill-rule":"evenodd",d:"M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z","clip-rule":"evenodd"})],-1)]),8,Fe),e("button",{onClick:G=>ee(o),class:"text-red-600 hover:text-red-900"},t[18]||(t[18]=[e("svg",{xmlns:"http://www.w3.org/2000/svg",class:"h-5 w-5",viewBox:"0 0 20 20",fill:"currentColor"},[e("path",{"fill-rule":"evenodd",d:"M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z","clip-rule":"evenodd"})],-1)]),8,Te)])])])}),128))])])])])])])]),e("div",qe,[l(pe,{links:m.purchase_orders.meta.links},null,8,["links"])])])]),l(n(R),{appear:"",show:S.value,as:"template"},{default:i(()=>[l(n(W),{as:"div",onClose:U,class:"relative z-50"},{default:i(()=>[l(n(z),{as:"template",enter:"duration-300 ease-out","enter-from":"opacity-0","enter-to":"opacity-100",leave:"duration-200 ease-in","leave-from":"opacity-100","leave-to":"opacity-0"},{default:i(()=>t[20]||(t[20]=[e("div",{class:"fixed inset-0 bg-black bg-opacity-25"},null,-1)])),_:1}),e("div",He,[e("div",Ee,[l(n(z),{as:"template",enter:"duration-300 ease-out","enter-from":"opacity-0 scale-95","enter-to":"opacity-100 scale-100",leave:"duration-200 ease-in","leave-from":"opacity-100 scale-100","leave-to":"opacity-0 scale-95"},{default:i(()=>[l(n(Y),{class:"w-full max-w-7xl transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-5xl sm:p-6"},{default:i(()=>[e("form",{onSubmit:de(Z,["prevent"]),class:"space-y-6"},[l(n(J),{as:"h3",class:"text-lg font-medium leading-6 text-gray-900"},{default:i(()=>[C(a(r.value.id?"Edit Purchase Order":"Create Purchase Order"),1)]),_:1}),e("div",Le,[e("div",null,[t[21]||(t[21]=e("label",{for:"po_number",class:"block text-sm font-medium text-gray-700"},"PO Number",-1)),g(e("input",{type:"text",id:"po_number","onUpdate:modelValue":t[5]||(t[5]=o=>r.value.po_number=o),required:"",placeholder:"Enter PO number",class:"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"},null,512),[[h,r.value.po_number]])]),e("div",null,[t[23]||(t[23]=e("label",{for:"supplier_id",class:"block text-sm font-medium text-gray-700"},"Supplier",-1)),g(e("select",{id:"supplier_id","onUpdate:modelValue":t[6]||(t[6]=o=>r.value.supplier_id=o),required:"",class:"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"},[t[22]||(t[22]=e("option",{value:""},"Select a supplier",-1)),(u(!0),d(P,null,N(m.suppliers,o=>(u(),d("option",{key:o.id,value:o.id},a(o.name),9,$e))),128))],512),[[D,r.value.supplier_id]])]),e("div",null,[t[24]||(t[24]=e("label",{for:"po_date",class:"block text-sm font-medium text-gray-700"},"PO Date",-1)),g(e("input",{type:"date",id:"po_date","onUpdate:modelValue":t[7]||(t[7]=o=>r.value.po_date=o),required:"",class:"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"},null,512),[[h,r.value.po_date]])])]),e("div",null,[t[25]||(t[25]=e("label",{for:"notes",class:"block text-sm font-medium text-gray-700"},"Notes",-1)),g(e("textarea",{id:"notes","onUpdate:modelValue":t[8]||(t[8]=o=>r.value.notes=o),rows:"2",placeholder:"Enter notes",class:"mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"},null,512),[[h,r.value.notes]])]),e("div",Ge,[e("button",{type:"button",disabled:f.value,onClick:U,class:"inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"}," Cancel ",8,Qe),e("button",{type:"submit",disabled:f.value,class:"inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"},a(r.value.id?f.value?"Updating...":"Update":f.value?"Creating...":"Create"),9,Re)])],32)]),_:1})]),_:1})])])]),_:1})]),_:1},8,["show"]),l(n(R),{appear:"",show:j.value,as:"template"},{default:i(()=>[l(n(W),{as:"div",class:"relative z-[60]",onClose:A},{default:i(()=>[l(n(z),{as:"template",enter:"ease-out duration-300","enter-from":"opacity-0","enter-to":"opacity-100",leave:"ease-in duration-200","leave-from":"opacity-100","leave-to":"opacity-0"},{default:i(()=>t[26]||(t[26]=[e("div",{class:"fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"},null,-1)])),_:1}),e("div",We,[e("div",Ye,[l(n(z),{as:"template",enter:"ease-out duration-300","enter-from":"opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95","enter-to":"opacity-100 translate-y-0 sm:scale-100",leave:"ease-in duration-200","leave-from":"opacity-100 translate-y-0 sm:scale-100","leave-to":"opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"},{default:i(()=>[l(n(Y),{class:"relative transform overflow-hidden rounded-lg bg-white px-4 pb-4 pt-5 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-5xl sm:p-6"},{default:i(()=>[e("div",{class:"absolute right-0 top-0 hidden pr-4 pt-4 sm:block"},[e("button",{type:"button",class:"rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2",onClick:A},t[27]||(t[27]=[e("span",{class:"sr-only"},"Close",-1),e("svg",{class:"h-6 w-6",fill:"none",viewBox:"0 0 24 24","stroke-width":"1.5",stroke:"currentColor"},[e("path",{"stroke-linecap":"round","stroke-linejoin":"round",d:"M6 18L18 6M6 6l12 12"})],-1)]))]),e("div",Je,[e("div",Ke,[l(n(J),{as:"h3",class:"text-lg font-semibold leading-6 text-gray-900"},{default:i(()=>[t[28]||(t[28]=C(" Purchase Order Items ")),p.value?(u(),d("span",Xe," (PO #"+a(p.value.po_number)+") ",1)):_("",!0)]),_:1}),e("div",Ze,[e("div",et,[p.value&&p.value.po_items?(u(),d("table",tt,[t[31]||(t[31]=e("thead",{class:"bg-gray-50"},[e("tr",null,[e("th",{scope:"col",class:"py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900"}," Product"),e("th",{scope:"col",class:"px-3 py-3.5 text-left text-sm font-semibold text-gray-900"}," Original Quantity"),e("th",{scope:"col",class:"px-3 py-3.5 text-left text-sm font-semibold text-gray-900"}," Quantity"),e("th",{scope:"col",class:"px-3 py-3.5 text-left text-sm font-semibold text-gray-900"}," Unit Cost"),e("th",{scope:"col",class:"px-3 py-3.5 text-left text-sm font-semibold text-gray-900"}," Total Cost")])],-1)),e("tbody",st,[(u(!0),d(P,null,N(p.value.po_items,o=>(u(),d("tr",{key:o.id,class:"hover:bg-gray-50"},[e("td",ot,a(o.item_description),1),e("td",lt,a(I(o.original_quantity)),1),e("td",at,a(I(o.quantity)),1),e("td",rt,a(M(o.unit_cost)),1),e("td",nt,a(M(o.total_cost)),1)]))),128)),p.value.po_items.length?_("",!0):(u(),d("tr",it,t[29]||(t[29]=[e("td",{colspan:"4",class:"px-3 py-4 text-sm text-center text-gray-500"}," No items found for this purchase order ",-1)])))]),p.value.po_items.length?(u(),d("tfoot",dt,[e("tr",null,[t[30]||(t[30]=e("td",{colspan:"3",class:"px-3 py-3.5 text-right text-sm font-semibold text-gray-900"}," Total:",-1)),e("td",ut,a(M(p.value.po_items.reduce((o,V)=>o+V.total_cost,0))),1)])])):_("",!0)])):_("",!0)])])])]),e("div",{class:"mt-5 sm:mt-4 sm:flex sm:flex-row-reverse"},[e("button",{type:"button",class:"mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto",onClick:A},"Close")])]),_:1})]),_:1})])])]),_:1})]),_:1},8,["show"])]),_:1})],64))}};export{yt as default};
