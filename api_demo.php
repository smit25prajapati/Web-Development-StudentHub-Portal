<?php
require_once __DIR__ . '/includes/init.php'; require_admin();
$pageTitle = 'AJAX student CRUD';
include __DIR__ . '/includes/header.php';
?>
<div class="page-heading"><p class="eyebrow">PRACTICAL 13</p><h1>AJAX student list</h1><p class="muted">This small Fetch API demo loads students from a PHP JSON endpoint and adds/deletes records without reloading the page.</p></div>
<div class="panel">
  <form id="addStudentForm" class="toolbar"><input id="studentName" placeholder="Student name" required minlength="2"><input id="studentEmail" type="email" placeholder="Email address" required><input id="studentCourse" placeholder="Course"><button class="btn" type="submit">Add student</button></form>
  <div id="apiMessage" aria-live="polite"></div><div id="studentList" class="api-list"><p class="muted">Loading students...</p></div>
</div>
<script>
const list = document.getElementById('studentList'), msg = document.getElementById('apiMessage');
async function loadStudents(){ list.innerHTML='<p class="muted">Loading...</p>'; try{const r=await fetch('api/students.php');const d=await r.json();if(!r.ok||!d.success)throw new Error(d.message||'Could not load records');list.innerHTML='';if(!d.data.length){list.innerHTML='<p class="muted">No students found.</p>';return;}d.data.forEach(s=>{const row=document.createElement('div');row.className='api-item';const info=document.createElement('div');const strong=document.createElement('b');strong.textContent=s.name;const detail=document.createElement('div');detail.className='muted';detail.textContent=s.email+' · '+(s.course||'Course not set');info.append(strong,detail);const del=document.createElement('button');del.className='btn btn-danger btn-small';del.textContent='Delete';del.onclick=()=>deleteStudent(s.id);row.append(info,del);list.append(row);});}catch(e){list.textContent=e.message;}}
document.getElementById('addStudentForm').addEventListener('submit',async e=>{e.preventDefault();msg.innerHTML='';const body={name:document.getElementById('studentName').value,email:document.getElementById('studentEmail').value,course:document.getElementById('studentCourse').value};try{const r=await fetch('api/students.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(body)});const d=await r.json();if(!r.ok||!d.success)throw new Error(d.message||'Could not add student');msg.innerHTML='<div class="alert success">Student added successfully.</div>';e.target.reset();loadStudents();}catch(err){msg.innerHTML='<div class="alert error"></div>';msg.querySelector('.alert').textContent=err.message;}});
async function deleteStudent(id){if(!confirm('Delete this student?'))return;try{const r=await fetch('api/students.php?id='+encodeURIComponent(id),{method:'DELETE'});const d=await r.json();if(!r.ok||!d.success)throw new Error(d.message||'Could not delete');msg.innerHTML='<div class="alert success">Student deleted.</div>';loadStudents();}catch(err){msg.innerHTML='<div class="alert error"></div>';msg.querySelector('.alert').textContent=err.message;}}
loadStudents();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>