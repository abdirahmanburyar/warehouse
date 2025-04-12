import{r as g,f as c,o as b,a as i,u as h,m as w,w as A,b as e,i as l,n as o,M as n,F as y,q as C,t as k}from"./app-lBFWFbO3.js";import{A as _}from"./AuthenticatedLayout-BAQR-N2z.js";import S from"./Index-CuCd0yod.js";import G from"./Index-CamMfC3p.js";import T from"./Index-BO7Ox-9A.js";import"./Modal-B7Ry9LA4.js";import"./InputLabel-H8ZK7EIA.js";import"./TextInput-BP2iDJ_P.js";import"./SelectInput-D0UO4_8K.js";import"./Pagination-B1ZOkbJe.js";import"./SortIcon-DegmTfiJ.js";import"./InputError-BLGuqF9p.js";import"./PrimaryButton-B4gQPJ4i.js";import"./SecondaryButton-CKTZP05A.js";import"./sweetalert2.esm.all-B0Dix5B2.js";import"./debounce-DS9xtVAQ.js";import"./TextArea-B11Z3-Ey.js";const O={class:"bg-white overflow-hidden shadow-sm sm:rounded-lg"},$={class:"p-6 text-gray-900"},M={class:"border-b border-gray-200 mb-4"},I={class:"-mb-px flex space-x-8"},j={class:"p-4 bg-gray-50 rounded-lg mb-4"},B={class:"mt-4 flex gap-4"},z=["onClick"],ee={__name:"Index",props:{approvals:Object,users:Object,roles:Array,permissions:Array,warehouses:Array,filters:Object,activeTab:{type:String,default:"General"}},setup(f){const s=f,r=g(s.activeTab),d=[{id:"purchase_order_item",name:"Purchase Order Items",model:"App\\Models\\PurchaseOrderItem"},{id:"order",name:"Orders",model:"App\\Models\\Order"},{id:"transfer",name:"Transfers",model:"App\\Models\\Transfer"}],p=g(d[0]),x=u=>{p.value=u};return(u,t)=>(b(),c(y,null,[i(h(w),{title:"Settings"}),i(_,{img:"/assets/images/settings.png",title:"Manager Your Settings",description:"Customize as it you like"},{default:A(()=>{var m;return[t[7]||(t[7]=e("h2",{class:"font-semibold text-xl text-gray-800 leading-tight"},"Settings",-1)),e("div",O,[e("div",$,[e("div",M,[e("nav",I,[e("button",{onClick:t[0]||(t[0]=a=>r.value="General"),class:o([r.value==="General"?"border-indigo-500 text-indigo-600":"border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300","whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"])}," General ",2),e("button",{onClick:t[1]||(t[1]=a=>r.value="users"),class:o([r.value==="users"?"border-indigo-500 text-indigo-600":"border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300","whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"])}," Users ",2),e("button",{onClick:t[2]||(t[2]=a=>r.value="roles"),class:o([r.value==="roles"?"border-indigo-500 text-indigo-600":"border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300","whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"])}," Roles ",2),e("button",{onClick:t[3]||(t[3]=a=>r.value="approval"),class:o([r.value==="approval"?"border-indigo-500 text-indigo-600":"border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300","whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm"])}," Approval Steps ",2)])]),l(e("div",{class:o(["transition-opacity duration-150",{"opacity-100":r.value==="General","opacity-0":r.value!=="General"}])},t[4]||(t[4]=[e("div",{class:"p-4 bg-gray-50 rounded-lg"},[e("h3",{class:"text-lg font-medium mb-4"},"General Settings"),e("p",{class:"text-gray-600"},"Configure your general application settings here.")],-1)]),2),[[n,r.value==="General"]]),l(e("div",{class:o(["transition-opacity duration-150",{"opacity-100":r.value==="users","opacity-0":r.value!=="users"}])},[i(S,{users:s.users,roles:s.roles,warehouses:s.warehouses,filters:s.filters},null,8,["users","roles","warehouses","filters"])],2),[[n,r.value==="users"]]),l(e("div",{class:o(["transition-opacity duration-150",{"opacity-100":r.value==="roles","opacity-0":r.value!=="roles"}])},[i(G,{roles:s.roles,permissions:s.permissions,filters:s.filters},null,8,["roles","permissions","filters"])],2),[[n,r.value==="roles"]]),l(e("div",{class:o(["transition-opacity duration-150",{"opacity-100":r.value==="approval","opacity-0":r.value!=="approval"}])},[e("div",j,[t[5]||(t[5]=e("h3",{class:"text-lg font-medium mb-2"},"Approval Steps Configuration",-1)),t[6]||(t[6]=e("p",{class:"text-gray-600"},"Configure approval workflows for different processes in the system. Each process can have multiple approval steps with specific roles and actions.",-1)),e("div",B,[(b(),c(y,null,C(d,a=>{var v;return e("button",{key:a.id,onClick:D=>x(a),class:o([((v=p.value)==null?void 0:v.id)===a.id?"bg-indigo-100 text-indigo-700 border-indigo-300":"bg-white text-gray-700 border-gray-300 hover:bg-gray-50","px-4 py-2 rounded-md border text-sm font-medium"])},k(a.name),11,z)}),64))])]),i(T,{filters:s.filters,approvals:s.approvals,roles:s.roles,"approvable-type":(m=p.value)==null?void 0:m.model,"approvable-id":null},null,8,["filters","approvals","roles","approvable-type"])],2),[[n,r.value==="approval"]])])])]}),_:1})],64))}};export{ee as default};
