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

app.login = function() {
  const loginForm = document.getElementById('loginForm');
  const loginView = document.getElementById('login-view');
  const dashboardView = document.getElementById('dashboard-view');

  loginForm?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // UI Feedback: Disable button to simulate loading
    const btn = loginForm.querySelector('button');
    const originalText = btn.textContent;
    btn.textContent = "Authenticating...";
    btn.disabled = true;

    const email = document.getElementById('email').value;

    try {
      // Simulate an asynchronous API call
      const response = await app.simulateServer(email);
      
      console.log("Server Response:", response);
      
      // Simple SPA view switch
      loginView.style.display = "none";
      dashboardView.style.display = "block";

app.createCard = function(project) {
  // 1. Create the main card container
  const card = document.createElement('div');
  card.className = "login-card";
  card.style.margin = "0";
  card.style.maxWidth = "none";

  // 2. Create the Title (H3)
  const title = document.createElement('h3');
  title.style.marginBottom = "10px";
  title.style.color = "var(--primary)";
  title.textContent = project.title;

  // 3. Create the Status text (P)
  const statusPara = document.createElement('p');
  statusPara.style.fontSize = "0.9rem";
  statusPara.style.color = "var(--gray)";
  statusPara.textContent = "Status: ";
  
  const statusStrong = document.createElement('strong');
  statusStrong.textContent = project.status;
  statusPara.appendChild(statusStrong);

  // 4. Create the Progress Bar Track (Container)
  const progressTrack = document.createElement('div');
  progressTrack.style.background = "#eee";
  progressTrack.style.height = "8px";
  progressTrack.style.borderRadius = "4px";
  progressTrack.style.marginTop = "15px";

  // 5. Create the Progress Fill (The blue part)
  const progressFill = document.createElement('div');
  progressFill.style.background = "var(--primary)";
  progressFill.style.width = project.progress;
  progressFill.style.height = "100%";
  progressFill.style.borderRadius = "4px";
  progressTrack.appendChild(progressFill);

  // 6. Create the Percentage Label (P)
  const percentLabel = document.createElement('p');
  percentLabel.style.fontSize = "0.8rem";
  percentLabel.style.marginTop = "5px";
  percentLabel.style.textAlign = "right";
  percentLabel.textContent = project.progress;

  // 7. Assemble the card
  card.appendChild(title);
  card.appendChild(statusPara);
  card.appendChild(progressTrack);
  card.appendChild(percentLabel);

  return card;
};

  document.getElementById('welcome-message').textContent = `Researcher Portal: ${response.user.email}`;
  app.renderDashboard(); // Generate the projects
  loginView.style.display = "none";
  dashboardView.style.display = "block";

      
    } catch (error) {
      alert("Login failed: " + error);
    } finally {
      btn.textContent = originalText;
      btn.disabled = false;
    }
  });

  // Simple Logout logic
  document.getElementById('logoutBtn')?.addEventListener('click', () => {
    loginView.style.display = "block";
    dashboardView.style.display = "none";
    loginForm.reset();
  });
};

// The Dummy Asynchronous Call
app.simulateServer = function(email) {
  return new Promise((resolve, reject) => {
    console.log("Contacting server...");
    
    setTimeout(() => {
      // Simulate success for any email containing '@'
      if (email.includes('@')) {
        resolve({ status: 200, user: email, token: "fake-jwt-123" });
      } else {
        reject("Invalid email format");
      }
    }, 1500); // 1.5 second delay
  });
};

// Dummy data for our POC
app.projects = [
  { id: 1, title: "Quantum Computing Logic", status: "Active", progress: "75%" },
  { id: 2, title: "Neural Network Optimization", status: "Review", progress: "40%" },
  { id: 3, title: "Biometric Security API", status: "Completed", progress: "100%" }
];

app.renderDashboard = function() {
  const grid = document.getElementById('project-grid');
  if (!grid) return;
  
  grid.innerHTML = ""; // Clear the grid

  app.projects.forEach(project => {
    // Call our new creation function
    const projectCard = app.createCard(project);
    // Inject the actual DOM Node into the grid
    grid.appendChild(projectCard);
  });
};


app.run=function(){
  app.build();
  app.mobile();
  app.login();
};

window.addEventListener("DOMContentLoaded",app.run);
