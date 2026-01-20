const app={};

app.menu=[
  {name:"Home",href:"#",active:true},
  {name:"Features",href:"#"},
  {name:"Products",href:"#",dropdown:[
    {name:"Lite",href:"#"},
    {name:"Pro",href:"#"},
    {name:"Enterprise",href:"#"}
  ]},
  {name:"Portal",href:"#",dropdown:[
    {name:"Customers",href:"#"},
    {name:"Clients",href:"#"},
    {name:"Staff",href:"#"}
  ]},
  {name:"Pricing",href:"#"},
  {name:"Contact",href:"#"}
];

app.createItems=function(c,items,mobile=false){
  items.forEach(i=>{
    let li=document.createElement("li");
    li.className="nav-item";
    if(i.dropdown){
      li.classList.add("dropdown");
      let a=document.createElement("a");
      a.className="nav-link dropdown-toggle";
      a.href=i.href||"#";
      a.textContent=i.name;
      if(i.active)a.classList.add("active");
      li.appendChild(a);
      let ul=document.createElement("ul");
      ul.className="dropdown-menu";
      if(mobile){
        ul.style.position="static";
        ul.style.border="none";
        ul.style.boxShadow="none";
        ul.style.margin="0";
        ul.style.paddingLeft="1.2rem";
      }
      i.dropdown.forEach(s=>{
        let sli=document.createElement("li");
        let sa=document.createElement("a");
        sa.className="dropdown-item";
        sa.href=s.href||"#";
        sa.textContent=s.name;
        sli.appendChild(sa);
        ul.appendChild(sli);
      });
      li.appendChild(ul);
    }else{
      let a=document.createElement("a");
      a.className="nav-link";
      a.href=i.href||"#";
      a.textContent=i.name;
      if(i.active)a.classList.add("active");
      li.appendChild(a);
    }
    c.appendChild(li);
  });
};

app.build=function(){
  let d=document.querySelector("#navbarNav .navbar-nav");
  if(d){
    d.innerHTML="";
    app.createItems(d,app.menu,false);
  }
  let m=document.querySelector("#mobileMenu .navbar-nav");
  if(m){
    m.innerHTML="";
    app.createItems(m,app.menu,true);
  }
};

app.mobile=function(){
  let t=document.querySelector(".navbar-toggler");
  let o=document.getElementById("mobileMenu");
  let b=document.getElementById("backdrop");
  let c=document.querySelector(".btn-close");
  if(!t||!o||!b||!c)return;
  function open(){o.classList.add("show");b.classList.add("show");}
  function close(){o.classList.remove("show");b.classList.remove("show");}
  t.addEventListener("click",open);
  c.addEventListener("click",close);
  b.addEventListener("click",close);
  document.addEventListener("keydown",e=>{if(e.key==="Escape")close();});
};

app.run=function(){
  app.build();
  app.mobile();
};

window.addEventListener("DOMContentLoaded",app.run);
