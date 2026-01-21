const app={};

app.menu=[
  {name:"Home",href:"#",active:true,pageId: "login-view"},
  {name:"Features",href:"#",pageId: "Features"},
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

app.showPage = function(pageId) {
  // 1. Define all possible "page" containers
  const pages = ['login-view', 'dashboard-view', 'Features'];
  
  // 2. Loop through and hide them all
  pages.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });

  // 3. Show the requested page
  const target = document.getElementById(pageId);
  if (target) {
    target.style.display = 'block';
    console.log(`Page Switched to: ${pageId}`);
  }
};

app.createItems = function(c, items, mobile = false) {
  items.forEach(i => {
    let li = document.createElement("li");
    li.className = "nav-item";

    if (i.dropdown) {
      li.classList.add("dropdown");
      let a = document.createElement("a");
      a.className = "nav-link dropdown-toggle";
      a.href = i.href || "#";
      a.textContent = i.name;
      if (i.active) a.classList.add("active");

      // Click handler for top-level dropdown parent
      a.onclick = function(e) {
        e.preventDefault();
        // If the dropdown name matches a page ID, show it
        if (i.name === "Features") app.showPage("Features");
      };

      li.appendChild(a);
      let ul = document.createElement("ul");
      ul.className = "dropdown-menu";
      
      if (mobile) {
        ul.style.position = "static";
        ul.style.border = "none";
        ul.style.boxShadow = "none";
        ul.style.margin = "0";
        ul.style.paddingLeft = "1.2rem";
      }

      i.dropdown.forEach(s => {
        let sli = document.createElement("li");
        let sa = document.createElement("a");
        sa.className = "dropdown-item";
        sa.href = s.href || "#";
        sa.textContent = s.name;

        sa.onclick = function(e) {
          e.preventDefault();
          console.log("Navigating to sub-item:", s.name);
          
          // Close mobile menu if open
          if(mobile) document.querySelector('.btn-close')?.click();
        };

        sli.appendChild(sa);
        ul.appendChild(sli);
      });
      li.appendChild(ul);
    } else {
      let a = document.createElement("a");
      a.className = "nav-link";
      a.href = i.href || "#";
      a.textContent = i.name;
      if (i.active) a.classList.add("active");

      // Click handler for standard links
      a.onclick = function(e) {
        e.preventDefault();
        
        // Router Logic
        if (i.name === "Features") {
          app.showPage("Features");
        } else if (i.name === "Home") {
          // If already logged in, show dashboard; otherwise show login
          const isLoggedIn = document.getElementById('dashboard-view').style.display === "block" || 
                             document.getElementById('welcome-message').textContent !== "";
          app.showPage(isLoggedIn ? 'dashboard-view' : 'login-view');
        }

        // Close mobile menu if open
        if(mobile) document.querySelector('.btn-close')?.click();
      };

      li.appendChild(a);
    }
    c.appendChild(li);
  });
};

app.projectFields = [
  { id: "title", label: "Project Title", type: "text", placeholder: "e.g. Quantum Logic", required: true },
  { id: "status", label: "Current Status", type: "text", placeholder: "e.g. Active", required: true },
  { id: "progress", label: "Progress (%)", type: "number", placeholder: "0-100", required: true }
];

app.createProjectForm = function() {
  const form = document.createElement('form');
  form.id = "newProjectForm";

  app.projectFields.forEach(field => {
    const group = document.createElement('div');
    group.className = "form-group";
    group.style.marginBottom = "1rem";

    const label = document.createElement('label');
    label.setAttribute('for', field.id);
    label.textContent = field.label;
    label.style.display = "block";
    label.style.marginBottom = "5px";

    const input = document.createElement('input');
    input.id = field.id;
    input.type = field.type;
    input.placeholder = field.placeholder;
    input.required = field.required;
    input.style.width = "100%";
    input.style.padding = "0.75rem";
    input.style.borderRadius = "0.375rem";
    input.style.border = "1px solid #ced4da";

    group.appendChild(label);
    group.appendChild(input);
    form.appendChild(group);
  });

  const submitBtn = document.createElement('button');
  submitBtn.type = "submit";
  submitBtn.className = "btn-login";
  submitBtn.textContent = "Add Project";
  submitBtn.style.marginTop = "10px";
  
  form.appendChild(submitBtn);

  // Handle Form Submission
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    
    // Create new project object from input values
    const newProject = {
      id: Date.now(), // Unique ID
      title: document.getElementById('title').value,
      status: document.getElementById('status').value,
      progress: document.getElementById('progress').value + "%"
    };

    app.projects.push(newProject); // Add to data array
    app.renderDashboard();         // Refresh the UI
    form.reset();                  // Clear the form
  });

  return form;
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
  const container = document.querySelector('#dashboard-view .container');
  const grid = document.getElementById('project-grid');
  
  if (!container || !grid) return;

  // 1. Check if the Form already exists to avoid duplicates
  let formWrapper = document.getElementById('form-wrapper');
  
  if (!formWrapper) {
    // Create a wrapper for the form to match your UI style
    formWrapper = document.createElement('div');
    formWrapper.id = 'form-wrapper';
    formWrapper.className = "login-card";
    formWrapper.style.marginBottom = "2rem";
    formWrapper.style.maxWidth = "100%";
    formWrapper.style.textAlign = "left";

    const formTitle = document.createElement('h3');
    formTitle.textContent = "Register New Research Project";
    formTitle.style.marginBottom = "1.5rem";
    
    // Use your programmatic form generator
    const projectForm = app.createProjectForm();
    
    formWrapper.appendChild(formTitle);
    formWrapper.appendChild(projectForm);
    
    // Insert the form at the top of the container, before the grid
    container.insertBefore(formWrapper, grid);
  }

  // 2. Clear the Grid and Render Project Cards
  grid.innerHTML = ""; 

  app.projects.forEach(project => {
    // Call the programmatic card creator
    const projectCard = app.createCard(project);
    grid.appendChild(projectCard);
  });
};


app.run=function(){
  app.build();
  app.mobile();
  app.login();
};

window.addEventListener("DOMContentLoaded",app.run);
