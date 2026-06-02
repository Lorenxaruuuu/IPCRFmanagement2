@php
    $activeTab = $activeTab ?? 'home';
    // Fetch forms
    $forms = [];
    try {
        $forms = \App\Models\Form::where('is_active', true)->latest('published_at')->get();
    } catch (\Exception $e) {}

    // Fetch notices
    $dbNotices = [];
    try {
        $dbNotices = \App\Models\Notice::active()->limit(10)->get();
    } catch (\Exception $e) {}

    // Bridge native PHP session and Laravel session if needed
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $sessionUser = session('user');
    if (!$sessionUser && isset($_SESSION['employee_id'])) {
        $sessionUser = [
            'employee_id' => $_SESSION['employee_id'],
            'name' => ($_SESSION['firstname'] ?? '') . ' ' . ($_SESSION['lastname'] ?? ''),
            'role' => $_SESSION['role'] ?? 'staff',
            'email' => $_SESSION['email'] ?? '',
            'firstname' => $_SESSION['firstname'] ?? '',
            'lastname' => $_SESSION['lastname'] ?? '',
        ];
        session(['user' => $sessionUser]);
    }

    // Fetch session-based user info
    $employeeId = $sessionUser['employee_id'] ?? '2024-00123';
    $email = $sessionUser['email'] ?? 'johnedoe@example.com';
    $name = $sessionUser['name'] ?? 'John Doe';
    $firstname = $sessionUser['firstname'] ?? 'John';
    $lastname = $sessionUser['lastname'] ?? 'Doe';
    $role = $sessionUser['role'] ?? 'staff';

    // Fetch DB record for the user to get permanent profile information and lock status
    $profileEdited = false;
    $dbUser = null;
    $userId = $sessionUser['id'] ?? null;
    $sessionEmployeeId = $sessionUser['employee_id'] ?? null;
    if ($userId || $sessionEmployeeId) {
        try {
            if ($userId) {
                $dbUser = \App\Models\User::with('position')->find($userId);
            } else {
                $dbUser = \App\Models\User::with('position')->where('employee_id', $sessionEmployeeId)->first();
            }
            
            if ($dbUser) {
                $profileEdited = (bool) $dbUser->profile_edited;
                $employeeId = $dbUser->employee_id ?? $sessionEmployeeId;
                $email = $dbUser->email ?? $email;
                $name = $dbUser->name ?? $name;
                $firstname = $dbUser->firstname ?? $firstname;
                $lastname = $dbUser->lastname ?? $lastname;
                $role = $dbUser->role ?? $role;
            }
        } catch (\Exception $e) {}
    }

    // Load custom fields from DB, then fallback to session, then fallback to defaults
    $birthday = ($dbUser && $dbUser->birthday) ? $dbUser->birthday : session('profile_birthday', 'January 15, 1990');
    $gender = ($dbUser && $dbUser->gender) ? $dbUser->gender : session('profile_gender', 'Male');
    $address = ($dbUser && $dbUser->address) ? $dbUser->address : session('profile_address', '123 Main Street, Davao City');
    $region = ($dbUser && $dbUser->region) ? $dbUser->region : session('profile_region', 'Region XI (Davao)');
    
    // Format birthday to YYYY-MM-DD for standard HTML5 date input calendar picker
    $birthdayInputVal = '';
    if ($birthday && $birthday !== 'January 15, 1990') {
        try {
            $birthdayInputVal = date('Y-m-d', strtotime($birthday));
        } catch (\Exception $e) {
            $birthdayInputVal = $birthday;
        }
    } else if ($birthday === 'January 15, 1990') {
        $birthdayInputVal = '1990-01-15';
    }
    $emailNotifications = session('pref_email_notifications', true);
    $pushNotifications = session('pref_push_notifications', true);
    $profileEmails = session('profile_emails', [$email]);

    // Check if there are unread notices
    $noticesReadAt = session('notices_read_at');
    $hasUnreadNotices = false;
    foreach ($dbNotices as $notice) {
        if (!$noticesReadAt || ($notice->posted_at && $notice->posted_at->gt($noticesReadAt))) {
            $hasUnreadNotices = true;
            break;
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>DSWD - User Profile</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Google Font: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .sidebar-gradient {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
        }
        .nav-item {
            transition: all 0.3s ease;
        }
        .card-hover {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        /* Premium IPCRF Sheet Styles */
        .ipcrf-table-wrapper {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            overflow: auto;
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.05);
        }
        .ipcrf-preview-table {
            border-collapse: collapse;
            font-size: 11px;
            color: #1e293b;
            min-width: 100%;
        }
        .ipcrf-preview-table td {
            border: 1px solid #cbd5e1;
            padding: 2px 4px;
            vertical-align: middle;
            position: relative;
        }
        .ipcrf-preview-table select,
        .ipcrf-preview-table input,
        .ipcrf-preview-table textarea {
            width: 100%;
            height: 100%;
            background: rgba(219, 234, 254, 0.45); /* soft blue */
            border: none;
            outline: none;
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            color: inherit;
            text-align: inherit;
            padding: 4px 6px;
            border-radius: 2px;
            transition: all 0.15s ease;
        }
        .ipcrf-preview-table select:focus,
        .ipcrf-preview-table input:focus,
        .ipcrf-preview-table textarea:focus {
            background: #ffffff;
            box-shadow: inset 0 0 0 2px #3b82f6;
            z-index: 10;
        }
        .ipcrf-preview-table select {
            cursor: pointer;
        }
        .ipcrf-preview-table select option {
            background: #ffffff;
            color: #1e293b;
        }
        .badge-ipcrf {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Spreadsheet Table Headers (Row/Col Indicators) */
        .ipcrf-hdr-corner,
        .ipcrf-hdr-col,
        .ipcrf-hdr-row {
            background: #f8fafc !important;
            color: #475569 !important;
            font-weight: 700 !important;
            font-size: 10px !important;
            text-align: center !important;
            border: 1px solid #cbd5e1 !important;
            user-select: none !important;
            position: relative !important;
        }
        .ipcrf-hdr-corner {
            width: 40px;
            height: 24px;
        }
        .ipcrf-hdr-col {
            height: 24px;
            vertical-align: middle !important;
        }
        .ipcrf-hdr-row {
            width: 40px;
            vertical-align: middle !important;
        }

        /* Resizers */
        .col-resizer {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 6px;
            cursor: col-resize;
            z-index: 10;
            background: transparent;
            transition: background 0.15s;
        }
        .col-resizer:hover, .col-resizer.dragging {
            background: #6366f1;
        }
        .row-resizer {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 6px;
            cursor: row-resize;
            z-index: 10;
            background: transparent;
            transition: background 0.15s;
        }
        .row-resizer:hover, .row-resizer.dragging {
            background: #6366f1;
        }

        /* Borderless inputs to look exactly like Google Sheets cells */
        .ipcrf-preview-table input,
        .ipcrf-preview-table textarea,
        .ipcrf-preview-table select {
            width: 100% !important;
            height: 100% !important;
            border: none !important;
            background: transparent !important;
            outline: none !important;
            font-family: inherit !important;
            font-size: inherit !important;
            font-weight: inherit !important;
            color: inherit !important;
            text-align: inherit !important;
            padding: 2px 4px !important;
            box-sizing: border-box !important;
            margin: 0 !important;
        }
        .ipcrf-preview-table input:focus,
        .ipcrf-preview-table textarea:focus,
        .ipcrf-preview-table select:focus {
            background: rgba(99, 102, 241, 0.05) !important;
            box-shadow: inset 0 0 0 2px #6366f1 !important;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-900 h-screen overflow-hidden flex" 
      x-data='{ 
          activeTab: "{{ $activeTab }}", 
          showLogoutModal: false, 
          showNotifications: false,
          showAddEmailModal: false,
          newEmail: "",
          emails: {!! json_encode(array_values(array_filter(array_unique($profileEmails)))) !!},
          removeEmail(index) {
              if (this.emails.length <= 1) {
                  alert("Requirement Error: You cannot remove this email address because it is your only email address. Please add another email address first before removing this one.");
                  return;
              }
              this.emails.splice(index, 1);
          },
          addEmail(email) {
              if (!email || !email.trim()) return;
              email = email.trim();
              if (!email.includes("@")) {
                  alert("Validation Error: Please enter a valid email address.");
                  return;
              }
              if (this.emails.includes(email)) {
                  alert("Validation Error: This email address is already added.");
                  return;
              }
              this.emails.push(email);
              this.showAddEmailModal = false;
              this.newEmail = "";
              $nextTick(() => lucide.createIcons());
          },
          // Performance tab filters
          semesterFilter: "all",
          yearFilter: "2026",
          // Performance sample data
          performances: [
              { id: 1, date: "02/25/2026", semester: "First", year: "2026" },
              { id: 2, date: "11/25/2026", semester: "Second", year: "2026" }
          ],
          get filteredPerformances() {
              return this.performances.filter(p => {
                  let semMatch = this.semesterFilter === "all" || p.semester.toLowerCase() === this.semesterFilter.toLowerCase();
                  let yearMatch = p.year === this.yearFilter;
                  return semMatch && yearMatch;
              });
          },

          // ─── IPCRF Template & Form filling states ──────────────────────
          assignedTemplates: [],
          submissions: [],
          ipcrfStats: { assigned: 0, drafts: 0, submitted: 0, approved: 0 },
          loadingIpcrf: false,
          fillingTemplate: null,
          submissionId: null,
          formHtml: "",
          formFields: [],
          answers: {},
          autoSaveInterval: null,
          savingStatus: "",

          async loadIpcrfData() {
              this.loadingIpcrf = true;
              try {
                  const res = await fetch("/my/dashboard-data");
                  const data = await res.json();
                  if (data.error) {
                      console.error(data.error);
                  } else {
                      this.assignedTemplates = data.assigned_templates || [];
                      this.submissions = data.submissions || [];
                      this.ipcrfStats = data.stats || { assigned: 0, drafts: 0, submitted: 0, approved: 0 };
                  }
              } catch (e) {
                  console.error("Error loading IPCRF data:", e);
              }
              this.loadingIpcrf = false;
              this.$nextTick(() => {
                  if (typeof lucide !== "undefined") lucide.createIcons();
              });
          },

          async startFillForm(templateId) {
              this.loadingIpcrf = true;
              if (this.autoSaveInterval) {
                  clearInterval(this.autoSaveInterval);
                  this.autoSaveInterval = null;
              }
              try {
                  const res = await fetch(`/my/templates/${templateId}/fill`);
                  if (!res.ok) {
                      const err = await res.json();
                      alert(err.error || "Access Denied.");
                      this.loadingIpcrf = false;
                      return;
                  }
                  const data = await res.json();
                  this.fillingTemplate = data.template;
                  this.submissionId = data.submission_id;
                  this.formHtml = data.html_table;
                  this.formFields = data.fields || [];
                  
                  // Initialize answers
                  this.answers = {};
                  this.formFields.forEach(f => {
                      this.answers[f.id] = f.current_value || "";
                  });

                  this.activeTab = "fillForm";
                  
                  // Render inputs into table cells
                  this.$nextTick(() => {
                      this.renderFormInputs();
                      setTimeout(() => {
                          if (typeof initSpreadsheetResizers === "function") {
                              initSpreadsheetResizers();
                          }
                      }, 50);
                  });

                  // Start autosave interval every 30 seconds
                  this.autoSaveInterval = setInterval(() => {
                      this.saveDraft(true);
                  }, 30000);

              } catch (e) {
                  console.error("Error fetching form:", e);
                  alert("Could not load form. Please try again.");
              }
              this.loadingIpcrf = false;
          },

          renderFormInputs() {
              document.querySelectorAll(".user-uploaded-picture").forEach(el => el.remove());

              this.formFields.forEach(field => {
                  const td = document.querySelector(`[data-cell="${field.cell_ref}"]`);
                  if (!td) return;

                  if (field.field_type === "picture") {
                      let currentPicData = null;
                      if (this.answers[field.id]) {
                          try {
                              currentPicData = JSON.parse(this.answers[field.id]);
                          } catch (e) {
                              currentPicData = { url: this.answers[field.id] };
                          }
                      }

                      td.className = "p-0 ipcrf-cell relative cursor-pointer";
                      td.style.minHeight = "40px";

                      const wrapper = document.createElement("div");
                      wrapper.className = "flex flex-col items-center justify-center w-full h-full min-h-[40px] text-indigo-400 hover:text-indigo-300 transition-colors p-2";
                      wrapper.innerHTML = `
                          <i class="fas fa-image text-sm mb-0.5"></i>
                          <span style="font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Add Picture</span>
                      `;

                      const fileInput = document.createElement("input");
                      fileInput.type = "file";
                      fileInput.accept = ".png,.jpg,.jpeg";
                      fileInput.style.display = "none";

                      // Trigger file explorer when clicking the cell
                      td.addEventListener("click", (e) => {
                          // Prevent triggering if clicking or dragging the image itself
                          if (e.target.tagName === "IMG" || e.target.classList.contains("user-uploaded-picture") || e.target.closest("img")) {
                              return;
                          }
                          fileInput.click();
                      });

                      fileInput.addEventListener("change", async (e) => {
                          const file = e.target.files[0];
                          if (!file) return;

                          const formData = new FormData();
                          formData.append("file", file);
                          formData.append("_token", "{{ csrf_token() }}");

                          wrapper.innerHTML = `
                              <i class="fas fa-spinner fa-spin text-sm mb-0.5"></i>
                              <span style="font-size: 8px; font-weight: 700; text-transform: uppercase;">Uploading...</span>
                          `;

                          try {
                              const res = await fetch(`/my/submissions/${this.submissionId}/upload-picture/${field.id}`, {
                                  method: "POST",
                                  body: formData,
                                  headers: {
                                      "X-Requested-With": "XMLHttpRequest"
                                  }
                              });
                              const data = await res.json();
                              if (data.success) {
                                  const initialData = {
                                      url: data.url,
                                      cell_ref: field.cell_ref,
                                      offsetX: 0,
                                      offsetY: 0,
                                      width: 120,
                                      height: 80
                                  };
                                  this.answers[field.id] = JSON.stringify(initialData);
                                  this.saveDraft(true);
                                  this.renderFormInputs();
                              } else {
                                  alert(data.message || "Upload failed");
                                  this.renderFormInputs();
                              }
                          } catch (err) {
                              console.error(err);
                              alert("Upload failed due to connection error");
                              this.renderFormInputs();
                          }
                      });

                      wrapper.appendChild(fileInput);
                      td.appendChild(wrapper);

                      if (currentPicData && currentPicData.url) {
                          const replaceBtn = document.createElement("button");
                          replaceBtn.innerHTML = `<i class="fas fa-sync"></i> Replace`;
                          replaceBtn.className = "absolute top-0 right-0 px-1 py-0.5 text-[8px] font-bold text-white bg-gray-800 bg-opacity-70 hover:bg-opacity-90 rounded z-20 pointer-events-auto";
                          replaceBtn.addEventListener("click", (e) => {
                              e.preventDefault();
                              e.stopPropagation();
                              fileInput.click();
                          });
                          td.appendChild(replaceBtn);

                          const imgEl = document.createElement("img");
                          imgEl.src = currentPicData.url;
                          imgEl.className = "user-uploaded-picture";
                          imgEl.style.position = "absolute";
                          imgEl.style.width = (currentPicData.width || 120) + "px";
                          imgEl.style.height = (currentPicData.height || 80) + "px";
                          imgEl.style.zIndex = "50";
                          imgEl.style.cursor = "move";
                          imgEl.style.border = "2px dashed #6366f1";
                          imgEl.style.boxShadow = "0 4px 12px rgba(0,0,0,0.15)";
                          imgEl.style.borderRadius = "4px";
                          imgEl.style.maxWidth = "none";

                          const targetCellRef = currentPicData.cell_ref || field.cell_ref;
                          const targetTd = document.querySelector(`[data-cell="${targetCellRef}"]`);

                          if (targetTd) {
                              targetTd.style.position = "relative";
                              imgEl.style.left = (currentPicData.offsetX || 0) + "px";
                              imgEl.style.top = (currentPicData.offsetY || 0) + "px";
                              targetTd.appendChild(imgEl);
                          } else {
                              td.appendChild(imgEl);
                              imgEl.style.left = "0px";
                              imgEl.style.top = "0px";
                          }

                          let isDragging = false;
                          let startX = 0;
                          let startY = 0;
                          let initialLeft = 0;
                          let initialTop = 0;
                          let currentCellRef = targetCellRef;

                          const onMouseDown = (e) => {
                              if (e.target !== imgEl) return;
                              e.preventDefault();
                              e.stopPropagation();

                              isDragging = true;
                              startX = e.clientX;
                              startY = e.clientY;
                              initialLeft = parseInt(imgEl.style.left) || 0;
                              initialTop = parseInt(imgEl.style.top) || 0;

                              document.addEventListener("mousemove", onMouseMove);
                              document.addEventListener("mouseup", onMouseUp);
                          };

                          const onMouseMove = (e) => {
                              if (!isDragging) return;
                              const dx = e.clientX - startX;
                              const dy = e.clientY - startY;
                              imgEl.style.left = (initialLeft + dx) + "px";
                              imgEl.style.top = (initialTop + dy) + "px";
                          };

                          const onMouseUp = (e) => {
                              if (!isDragging) return;
                              isDragging = false;

                              document.removeEventListener("mousemove", onMouseMove);
                              document.removeEventListener("mouseup", onMouseUp);

                              imgEl.style.display = "none";
                              const droppedOn = document.elementFromPoint(e.clientX, e.clientY);
                              imgEl.style.display = "block";

                              const cellTd = droppedOn ? droppedOn.closest("td[data-cell]") : null;

                              if (cellTd) {
                                  const newCellRef = cellTd.getAttribute("data-cell");
                                  const cellRect = cellTd.getBoundingClientRect();
                                  const currentImgRect = imgEl.getBoundingClientRect();

                                  const offsetX = Math.round(currentImgRect.left - cellRect.left);
                                  const offsetY = Math.round(currentImgRect.top - cellRect.top);

                                  cellTd.style.position = "relative";
                                  cellTd.appendChild(imgEl);

                                  imgEl.style.left = offsetX + "px";
                                  imgEl.style.top = offsetY + "px";

                                  currentCellRef = newCellRef;

                                  this.answers[field.id] = JSON.stringify({
                                      url: currentPicData.url,
                                      cell_ref: newCellRef,
                                      offsetX: offsetX,
                                      offsetY: offsetY,
                                      width: parseInt(imgEl.style.width),
                                      height: parseInt(imgEl.style.height)
                                  });

                                  this.saveDraft(true);
                              } else {
                                  imgEl.style.left = initialLeft + "px";
                                  imgEl.style.top = initialTop + "px";
                              }
                          };

                          imgEl.addEventListener("mousedown", onMouseDown);
                      }
                      return;
                  }

                  // Preserve any drawing/image overlays inside the cell
                  const drawings = td.querySelectorAll("img");
                  td.innerHTML = ""; // Clear the server-rendered badge
                  if (drawings.length > 0) {
                      drawings.forEach(img => td.appendChild(img));
                  }
                  td.className = "p-0 ipcrf-cell"; // Set correct class and clear padding
                  
                  let inputEl;
                  const isReadonly = field.field_type.startsWith("autofill_") || field.field_type === "readonly";

                  if (field.field_type === "textarea") {
                      inputEl = document.createElement("textarea");
                      inputEl.rows = 2;
                  } else if (field.field_type === "dropdown") {
                      inputEl = document.createElement("select");
                      const optDefault = document.createElement("option");
                      optDefault.value = "";
                      optDefault.textContent = "-- Select --";
                      inputEl.appendChild(optDefault);

                      if (field.field_options && Array.isArray(field.field_options)) {
                          field.field_options.forEach(opt => {
                              const o = document.createElement("option");
                              o.value = opt;
                              o.textContent = opt;
                              inputEl.appendChild(o);
                          });
                      }
                  } else if (field.field_type === "rating") {
                      inputEl = document.createElement("select");
                      const optDefault = document.createElement("option");
                      optDefault.value = "";
                      optDefault.textContent = "-- Select Rating --";
                      inputEl.appendChild(optDefault);

                      [5, 4, 3, 2, 1].forEach(score => {
                          const o = document.createElement("option");
                          o.value = score;
                          o.textContent = score + " (" + this.ratingLabel(score) + ")";
                          inputEl.appendChild(o);
                      });
                  } else {
                      inputEl = document.createElement("input");
                      inputEl.type = field.field_type === "number" ? "number" : "text";
                      if (field.field_type === "number") {
                          inputEl.step = "any";
                      }
                  }

                  inputEl.name = `field_${field.id}`;
                  inputEl.value = this.answers[field.id] || "";
                  if (isReadonly) {
                      inputEl.readOnly = true;
                      inputEl.classList.add("cursor-not-allowed", "opacity-75");
                      inputEl.style.background = "rgba(241, 245, 249, 0.6)"; 
                  }

                  // Handle change events to keep state synced
                  const updateVal = (e) => {
                      this.answers[field.id] = e.target.value;
                      this.saveDraft(false); // background save
                  };
                  inputEl.addEventListener("input", updateVal);
                  inputEl.addEventListener("change", updateVal);

                  td.appendChild(inputEl);
              });
          },

          ratingLabel(score) {
              const labels = { 5: "Outstanding", 4: "Very Satisfactory", 3: "Satisfactory", 2: "Unsatisfactory", 1: "Poor" };
              return labels[score] || "";
          },

          async saveDraft(isSilent = false) {
              if (!this.fillingTemplate) return;
              if (!isSilent) this.savingStatus = "Saving draft...";
              try {
                  const res = await fetch(`/my/templates/${this.fillingTemplate.id}/draft`, {
                      method: "POST",
                      headers: {
                          "Content-Type": "application/json",
                          "X-CSRF-TOKEN": "{{ csrf_token() }}",
                          "X-Requested-With": "XMLHttpRequest"
                      },
                      body: JSON.stringify({ answers: this.answers })
                  });
                  const data = await res.ok ? await res.json() : {};
                  if (data.success) {
                      if (!isSilent) this.savingStatus = "Draft saved successfully";
                      else this.savingStatus = "Auto-saved at " + new Date().toLocaleTimeString();
                  } else {
                      this.savingStatus = "Error auto-saving";
                  }
              } catch (e) {
                  this.savingStatus = "Connection error";
              }
              if (!isSilent) {
                  setTimeout(() => { this.savingStatus = ""; }, 3000);
              }
          },

          async submitForm() {
              if (!this.fillingTemplate) return;
              
              // Simple client side validation of required fields
              let missingRequired = [];
              this.formFields.forEach(f => {
                  if (f.is_required && !this.answers[f.id]) {
                      missingRequired.push(f.field_label || f.cell_ref);
                  }
              });

              if (missingRequired.length > 0) {
                  alert("Requirement Error: Please fill in the following required fields before submitting:\n• " + missingRequired.join("\n• "));
                  return;
              }

              if (!confirm("Are you sure you want to submit your IPCRF? Once submitted, it will be locked and sent to the Administrator for review and approval.")) {
                  return;
              }

              this.loadingIpcrf = true;
              try {
                  const res = await fetch(`/my/templates/${this.fillingTemplate.id}/submit`, {
                      method: "POST",
                      headers: {
                          "Content-Type": "application/json",
                          "X-CSRF-TOKEN": "{{ csrf_token() }}",
                          "X-Requested-With": "XMLHttpRequest"
                      },
                      body: JSON.stringify({ answers: this.answers })
                  });
                  const data = await res.json();
                  if (data.success) {
                      alert("Your IPCRF has been submitted successfully for administrator review!");
                      this.exitForm();
                  } else {
                      alert("Error submitting: " + data.message);
                  }
              } catch (e) {
                  alert("Connection error during submission.");
              }
              this.loadingIpcrf = false;
          },

          exitForm() {
              if (this.autoSaveInterval) {
                  clearInterval(this.autoSaveInterval);
                  this.autoSaveInterval = null;
              }
              this.fillingTemplate = null;
              this.submissionId = null;
              this.formHtml = "";
              this.formFields = [];
              this.answers = {};
              this.activeTab = "ipcrf";
              this.loadIpcrfData();
          },

          formatDate(dateStr) {
              if (!dateStr) return "—";
              try {
                  const d = new Date(dateStr);
                  if (isNaN(d.getTime())) return dateStr;
                  return d.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
              } catch (e) {
                  return dateStr;
              }
          },

          statusLabel(status) {
              const labels = {
                  draft: "Draft",
                  submitted: "Submitted",
                  under_review: "Under Review",
                  approved: "Approved",
                  rejected: "Rejected"
              };
              return labels[status] || status;
          },

          statusBadgeClass(status) {
              const classes = {
                  draft: "bg-slate-100 text-slate-700",
                  submitted: "bg-blue-100 text-blue-700",
                  under_review: "bg-amber-100 text-amber-700",
                  approved: "bg-green-100 text-green-700",
                  rejected: "bg-red-100 text-red-700"
              };
              return classes[status] || "bg-slate-100 text-slate-700";
          }
      }'
      x-init="$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); loadIpcrfData(); });">

    <!-- SIDEBAR -->
    <aside class="fixed left-0 top-0 h-full w-64 sidebar-gradient text-white hidden md:flex flex-col shadow-xl z-20">
        <!-- Logo Header -->
        <div class="p-6 flex items-center gap-3 border-b border-gray-700">
            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center overflow-hidden">
                <img src="{{ asset('images/logo.png') }}" class="w-full h-full object-contain" alt="DSWD Logo">
            </div>
            <div>
                <h1 class="font-bold text-lg leading-tight text-white">DSWD</h1>
                <p class="text-xs text-gray-400">IPCR Management</p>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 py-6 px-0 space-y-1">
            <button @click="activeTab = 'home'"
                    class="w-full flex items-center gap-3 px-6 py-3.5 text-base transition-all text-left cursor-pointer border-l-4 focus:outline-none"
                    :class="activeTab === 'home' ? 'bg-white/10 border-blue-500 text-white font-semibold shadow-sm' : 'border-transparent text-gray-300 hover:bg-white/5 hover:text-white'">
                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                Dashboard
            </button>
            <button @click="activeTab = 'ipcrf'; loadIpcrfData()"
                    class="w-full flex items-center gap-3 px-6 py-3.5 text-base transition-all text-left cursor-pointer border-l-4 focus:outline-none"
                    :class="activeTab === 'ipcrf' ? 'bg-white/10 border-blue-500 text-white font-semibold shadow-sm' : 'border-transparent text-gray-300 hover:bg-white/5 hover:text-white'">
                <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                My IPCRF Forms
            </button>
            <a href="{{ route('performance.index') }}" 
                    class="w-full flex items-center gap-3 px-6 py-3.5 text-base transition-all text-left cursor-pointer border-l-4 {{ $activeTab === 'performance' ? 'bg-white/10 border-blue-500 text-white font-semibold shadow-sm' : 'border-transparent text-gray-300 hover:bg-white/5 hover:text-white' }}">
                <i data-lucide="trending-up" class="w-5 h-5"></i>
                Performance History
            </a>
            <button @click="activeTab = 'settings'"
                    class="w-full flex items-center gap-3 px-6 py-3.5 text-base transition-all text-left cursor-pointer border-l-4 focus:outline-none"
                    :class="activeTab === 'settings' ? 'bg-white/10 border-blue-500 text-white font-semibold shadow-sm' : 'border-transparent text-gray-300 hover:bg-white/5 hover:text-white'">
                <i data-lucide="settings" class="w-5 h-5"></i>
                <span>Settings</span>
            </button>
        </nav>

        <!-- User Profile info -->
        <div class="p-4 border-t border-gray-700">
            <div class="flex items-center gap-3 px-4 py-3">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <i data-lucide="user" class="w-4 h-4 text-white"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold truncate text-white">{{ $name }}</p>
                    <p class="text-xs truncate text-blue-300 font-medium">{{ $dbUser?->position?->name ?? 'No Position Assigned' }}</p>
                </div>
            </div>

            <!-- Hidden Logout Form -->
            <form x-ref="logoutForm" method="POST" action="{{ route('logout') }}" style="display: none;">
                @csrf
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="md:ml-64 flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
        
        <!-- Top Navbar -->
        <header class="glass-panel h-16 flex items-center justify-between px-6 border-b">
            <h2 class="text-xl font-semibold text-slate-800" x-text="
                activeTab === 'home' ? 'Dashboard' : 
                activeTab === 'performance' ? 'Performance History' : 
                activeTab === 'settings' ? 'Settings' : 
                activeTab === 'ipcrf' ? 'My IPCRF Forms' : 
                activeTab === 'fillForm' ? 'Fill IPCRF Template' : ''
            "></h2>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <button @click="showNotifications = !showNotifications" class="relative p-2 text-slate-400 hover:text-blue-600 transition-colors">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        @if($hasUnreadNotices)
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border border-white animate-pulse"></span>
                        @endif
                    </button>

                    <!-- NOTIFICATIONS DROPDOWN (positioned absolutely so it overlays content) -->
                    <div x-show="showNotifications"
                         x-transition
                         @click.away="showNotifications = false"
                         class="absolute right-0 top-full mt-2 w-80 bg-white rounded-xl shadow-2xl border border-slate-200 overflow-hidden z-50"
                         style="display: none;">
                        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                            <h3 class="font-semibold text-slate-800">Announcements</h3>
                            <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Mark read</button>
                            </form>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            @forelse($dbNotices as $notice)
                                <div class="p-4 border-b border-slate-100 hover:bg-slate-50 transition">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="bell" class="w-4 h-4"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-slate-800 text-sm">{{ $notice->subject }}</p>
                                            <p class="text-slate-600 text-xs mt-1">{{ Str::limit($notice->content, 100) }}</p>
                                            <p class="text-slate-400 text-[10px] mt-2">{{ $notice->posted_at ? $notice->posted_at->diffForHumans() : '' }}</p>
                                        </div>
                                        <span class="text-[10px] px-2 py-1 rounded-full font-medium 
                                            @if($notice->priority === 'High') bg-red-100 text-red-700
                                            @elseif($notice->priority === 'Medium') bg-yellow-100 text-yellow-700
                                            @else bg-green-100 text-green-700 @endif">
                                            {{ $notice->priority }}
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 border-b border-slate-100 bg-blue-50/50 hover:bg-blue-50 transition">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="bell" class="w-4 h-4"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-slate-800 text-sm">System Announcement</p>
                                            <p class="text-slate-600 text-xs mt-1">New IPCRF form templates available</p>
                                        </div>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <button @click="showLogoutModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-all flex items-center gap-2 font-medium text-sm cursor-pointer shadow-sm shadow-blue-600/10">
                    <i data-lucide="log-out" class="w-4 h-4"></i>Logout
                </button>
            </div>
        </header>

        <!-- Main Body Content -->
        <main class="flex-1 overflow-y-auto p-6">
            
            <!-- Global Session Alerts -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center gap-3 shadow-sm">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600"></i>
                    <span class="font-medium text-sm">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl space-y-1 shadow-sm">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-600"></i>
                        <span class="font-semibold text-sm">Please correct the following errors:</span>
                    </div>
                    <ul class="list-disc pl-8 text-xs space-y-1 font-medium text-red-700">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- HOME TAB (Dashboard) -->
            <div x-show="activeTab === 'home'" x-transition class="space-y-6 w-full">
                <!-- Welcome Banner -->
                <div class="mb-2 flex flex-wrap justify-between items-center gap-3">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-800">Welcome, {{ $name }}</h1>
                        <p class="text-sm text-slate-500 mt-1">{{ now()->format('D, d F Y') }}</p>
                    </div>
                    @if($hasUnreadNotices)
                    <span class="flex items-center gap-2 text-sm font-semibold text-red-600 bg-red-50 px-4 py-2 rounded-xl border border-red-100">
                        <i data-lucide="bell" class="w-4 h-4"></i>
                        New Announcements
                    </span>
                    @endif
                </div>

                <!-- 2-column Dashboard Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Main Area: IPCRF Templates (2 columns width) -->
                    <div class="lg:col-span-2 glass-panel rounded-2xl shadow-sm overflow-hidden card-hover flex flex-col">
                        <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center">
                                <i data-lucide="file-spreadsheet" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">Available IPCRF Templates</h3>
                                <p class="text-sm text-slate-500">Download the required official IPCRF templates.</p>
                            </div>
                        </div>
                        <div class="divide-y divide-slate-100 flex-1 overflow-y-auto" style="max-height:500px">
                            @forelse($forms as $form)
                                <div class="p-5 flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="file-text" class="w-5 h-5"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-800 text-base truncate">{{ $form->name }}</p>
                                            <p class="text-slate-500 text-sm mt-0.5 truncate">{{ $form->description ?? 'Official IPCRF document template' }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('forms.download', $form->id) }}"
                                       class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-xl font-semibold text-sm flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap flex-shrink-0 shadow-sm">
                                        <i data-lucide="download" class="w-4 h-4"></i>
                                        Download
                                    </a>
                                </div>
                            @empty
                                <div class="p-10 text-center text-slate-400">
                                    <i data-lucide="file-text" class="w-10 h-10 mx-auto mb-3 opacity-40"></i>
                                    <p class="text-base font-medium">No templates available yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Side Area: Announcements & Notifications (1 column width) -->
                    <div class="glass-panel rounded-2xl shadow-sm overflow-hidden card-hover flex flex-col">
                        <div class="p-6 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center">
                                    <i data-lucide="bell" class="w-5 h-5"></i>
                                </div>
                                <h3 class="font-bold text-slate-800 text-lg">Announcements</h3>
                            </div>
                            <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-semibold px-2 py-1 bg-blue-50 rounded-lg transition-colors cursor-pointer">Mark all read</button>
                            </form>
                        </div>
                        <div class="divide-y divide-slate-100 flex-1 overflow-y-auto" style="max-height:500px">
                            @forelse($dbNotices as $notice)
                                @php
                                    $priorityBadge = match(strtolower($notice->priority)) {
                                        'high' => 'bg-red-100 text-red-700',
                                        'medium' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-green-100 text-green-700',
                                    };
                                @endphp
                                <div class="p-5 hover:bg-slate-50 transition-colors group relative">
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <p class="font-semibold text-slate-800 text-sm leading-snug pr-4">{{ $notice->subject }}</p>
                                        <span class="text-[10px] px-2 py-0.5 rounded-md font-bold uppercase tracking-wider whitespace-nowrap {{ $priorityBadge }}">{{ $notice->priority }}</span>
                                    </div>
                                    <p class="text-slate-600 text-sm leading-relaxed mb-3">{{ $notice->content }}</p>
                                    <div class="flex items-center justify-between">
                                        <p class="text-slate-400 text-xs font-medium flex items-center gap-1.5">
                                            <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                                            {{ $notice->posted_at ? $notice->posted_at->diffForHumans() : '' }}
                                        </p>
                                        @if(!$notice->is_read)
                                            <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-10 text-center text-slate-400">
                                    <i data-lucide="bell-off" class="w-10 h-10 mx-auto mb-3 opacity-40"></i>
                                    <p class="text-base font-medium">No announcements yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>

            <!-- PERFORMANCE HISTORY TAB -->
            <div x-show="activeTab === 'performance'" x-transition class="space-y-6 w-full" style="display: none;">
                <div class="glass-panel rounded-2xl shadow-sm p-8 card-hover">
                    <!-- Filters Section -->
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8 pb-6 border-b border-slate-200">
                        <div class="flex flex-wrap items-center gap-6">
                            <div class="flex flex-col">
                                <label class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Semester</label>
                                <select x-model="semesterFilter" class="px-5 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium text-base focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                                    <option value="all">All Semesters</option>
                                    <option value="First">First</option>
                                    <option value="Second">Second</option>
                                </select>
                            </div>

                            <div class="flex flex-col">
                                <label class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Year</label>
                                <select x-model="yearFilter" class="px-5 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 font-medium text-base focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer">
                                    <option value="2026">2026</option>
                                    <option value="2025">2025</option>
                                    <option value="2024">2024</option>
                                </select>
                            </div>
                        </div>

                        <a :href="'{{ url('/performance/download-report') }}?semester=' + semesterFilter + '&year=' + yearFilter" 
                           class="bg-slate-900 text-white hover:bg-slate-800 px-6 py-3 rounded-xl font-medium text-base flex items-center gap-2 shadow-sm transition-all duration-200 cursor-pointer">
                            <i data-lucide="download" class="w-5 h-5"></i>
                            Download Report
                        </a>
                    </div>

                    <!-- Performance Table -->
                    <div class="overflow-x-auto rounded-xl border border-slate-100">
                        <table class="w-full text-left border-collapse text-base">
                            <thead>
                                <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 text-sm font-semibold tracking-wider uppercase">
                                    <th class="px-8 py-5">Evaluation Date</th>
                                    <th class="px-8 py-5">Semester</th>
                                    <th class="px-8 py-5">Year</th>
                                    <th class="px-8 py-5 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="p in filteredPerformances" :key="p.id">
                                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                        <td class="px-8 py-5 font-medium text-slate-800" x-text="p.date"></td>
                                        <td class="px-8 py-5 text-slate-600" x-text="p.semester"></td>
                                        <td class="px-8 py-5 text-slate-600" x-text="p.year"></td>
                                        <td class="px-8 py-5 text-right">
                                            <a :href="'{{ url('/performance') }}/' + p.id + '/view'" 
                                               class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">
                                                View Details
                                            </a>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="filteredPerformances.length === 0">
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                            <i data-lucide="activity" class="w-12 h-12 mx-auto mb-3 opacity-40"></i>
                                            <p class="text-base font-medium">No performance records match your filters.</p>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



            <!-- SETTINGS TAB -->
            <div x-show="activeTab === 'settings'" x-transition class="space-y-6 w-full" style="display: none;">
                <!-- 2-column Settings Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Profile Card -->
                    <div class="lg:col-span-2 glass-panel rounded-2xl shadow-sm overflow-hidden w-full card-hover h-fit">
                    <!-- Header Banner -->
                    <div class="h-36 bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900"></div>
                    
                    <div class="px-8 pb-8 relative">
                        <!-- Profile Avatar & Info -->
                        <div class="flex flex-col sm:flex-row items-center gap-6 -mt-16 mb-8 text-center sm:text-left">
                            <div class="w-28 h-28 rounded-full border-4 border-white shadow-lg overflow-hidden bg-slate-100 flex-shrink-0">
                                <img src="https://i.pravatar.cc/150?img=5" alt="Profile Picture" class="w-full h-full object-cover">
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="text-3xl font-bold text-white leading-tight">{{ $name }}</h2>
                                <p class="text-base text-slate-500 mt-2 flex items-center justify-center sm:justify-start gap-2 font-medium">
                                    <i data-lucide="mail" class="w-4 h-4 text-slate-400"></i>
                                    <span>{{ $email }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Profile Form -->
                        @if($profileEdited)
                            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl flex items-center gap-3 shadow-sm">
                                <i data-lucide="lock" class="w-5 h-5 text-amber-600"></i>
                                <span class="font-medium text-sm">Personal details are locked because they have already been edited once.</span>
                            </div>
                        @endif
                        <form id="profileForm" method="POST" action="{{ route('profile.update') }}" class="space-y-10" onsubmit="return confirmProfileUpdate(event)">
                            @csrf

                            <!-- Section: Personal Information -->
                            <div>
                                <div class="border-b border-slate-200/60 pb-4 mb-6">
                                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                        <i data-lucide="user-cog" class="w-5 h-5 text-blue-600"></i>
                                        <span>Personal Information</span>
                                    </h3>
                                    <p class="text-sm text-slate-500 mt-0.5">Manage your personal identification details.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="flex flex-col">
                                        <label for="full_name" class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Full Name</label>
                                        <div class="relative flex items-center">
                                            <i data-lucide="user" class="w-5 h-5 text-slate-400 absolute left-4"></i>
                                            <input type="text" id="full_name" name="full_name" value="{{ $name }}" placeholder="Enter full name" required {{ $profileEdited ? 'readonly' : '' }}
                                                   class="w-full pl-12 pr-5 py-3.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-base {{ $profileEdited ? 'opacity-70 cursor-not-allowed bg-slate-100' : '' }}">
                                        </div>
                                    </div>

                                    <div class="flex flex-col">
                                        <label for="birthday" class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Birthday</label>
                                        <div class="relative flex items-center">
                                            <i data-lucide="calendar" class="w-5 h-5 text-slate-400 absolute left-4"></i>
                                            <input type="date" id="birthday" name="birthday" value="{{ $birthdayInputVal }}" placeholder="Select birthday" required {{ $profileEdited ? 'readonly' : '' }}
                                                   class="w-full pl-12 pr-5 py-3.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-base {{ $profileEdited ? 'opacity-70 cursor-not-allowed bg-slate-100' : '' }}">
                                        </div>
                                    </div>

                                    <div class="flex flex-col">
                                        <label for="gender" class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Gender</label>
                                        <div class="relative flex items-center">
                                            <i data-lucide="smile" class="w-5 h-5 text-slate-400 absolute left-4"></i>
                                            <input type="text" id="gender" name="gender" value="{{ $gender }}" placeholder="Select gender" {{ $profileEdited ? 'readonly' : '' }}
                                                   class="w-full pl-12 pr-5 py-3.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-base {{ $profileEdited ? 'opacity-70 cursor-not-allowed bg-slate-100' : '' }}">
                                        </div>
                                    </div>

                                    <div class="flex flex-col">
                                        <label class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Position / Designation</label>
                                        <div class="relative flex items-center">
                                            <i data-lucide="briefcase" class="w-5 h-5 text-slate-400 absolute left-4"></i>
                                            <input type="text" readonly value="{{ $dbUser?->position?->name ?? 'None Assigned' }}" 
                                                   class="w-full pl-12 pr-5 py-3.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed opacity-75 focus:outline-none font-medium text-base">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Address & Regional details -->
                            <div>
                                <div class="border-b border-slate-200/60 pb-4 mb-6">
                                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                        <i data-lucide="map-pin" class="w-5 h-5 text-blue-600"></i>
                                        <span>Address & Regional Details</span>
                                    </h3>
                                    <p class="text-sm text-slate-500 mt-0.5">Manage your physical location and regional assignment.</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="flex flex-col">
                                        <label for="address" class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Address</label>
                                        <div class="relative flex items-center">
                                            <i data-lucide="navigation" class="w-5 h-5 text-slate-400 absolute left-4"></i>
                                            <input type="text" id="address" name="address" value="{{ $address }}" placeholder="Enter address" {{ $profileEdited ? 'readonly' : '' }}
                                                   class="w-full pl-12 pr-5 py-3.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-base {{ $profileEdited ? 'opacity-70 cursor-not-allowed bg-slate-100' : '' }}">
                                        </div>
                                    </div>

                                    <div class="flex flex-col md:col-span-1">
                                        <label for="region" class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Region</label>
                                        <div class="relative flex items-center">
                                            <i data-lucide="globe" class="w-5 h-5 text-slate-400 absolute left-4"></i>
                                            <input type="text" id="region" name="region" value="{{ $region }}" placeholder="Enter region" {{ $profileEdited ? 'readonly' : '' }}
                                                   class="w-full pl-12 pr-5 py-3.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-base {{ $profileEdited ? 'opacity-70 cursor-not-allowed bg-slate-100' : '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Email Addresses -->
                            <div>
                                <div class="border-b border-slate-200/60 pb-4 mb-6">
                                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                        <i data-lucide="mails" class="w-5 h-5 text-blue-600"></i>
                                        <span>Email Addresses</span>
                                    </h3>
                                    <p class="text-sm text-slate-500 mt-0.5">Manage primary and secondary communication channels.</p>
                                </div>
                                
                                <div class="space-y-3 max-w-3xl">
                                    <template x-for="(emailItem, index) in emails" :key="index">
                                        <div class="flex justify-between items-center p-5 bg-slate-50/50 border border-slate-200/60 rounded-xl hover:bg-slate-100/50 transition-all group">
                                            <div class="flex items-center gap-3">
                                                <i data-lucide="mail" class="w-5 h-5 text-slate-400"></i>
                                                <span class="text-base font-medium text-slate-700" x-text="emailItem"></span>
                                            </div>
                                            <input type="hidden" name="emails[]" :value="emailItem">
                                            <template x-if="index === 0">
                                                <input type="hidden" name="email" :value="emailItem">
                                            </template>
                                            <button type="button" @click="removeEmail(index)" class="flex items-center gap-1.5 text-sm font-semibold text-red-600 hover:text-red-800 cursor-pointer transition-colors hover:underline">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                <span>Remove</span>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <button type="button" @click="showAddEmailModal = true" class="mt-4 bg-blue-50 text-blue-600 hover:bg-blue-100 px-5 py-3 rounded-xl font-semibold text-base flex items-center gap-2 transition-all duration-200 active:scale-95 cursor-pointer">
                                    <i data-lucide="plus" class="w-5 h-5"></i>
                                    Add Email Address
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-1 glass-panel rounded-2xl shadow-sm p-8 card-hover flex flex-col h-fit">
                    <h3 class="text-2xl font-bold text-slate-800 mb-6">Account Settings</h3>
                    
                    <div class="space-y-8">
                        <!-- Security section (Separate Form) -->
                        <form method="POST" action="{{ route('profile.changePassword') }}" x-data="{ showCurrent: false, showNew: false }">
                            @csrf
                            <div>
                                <h4 class="font-bold text-slate-800 text-lg mb-2">Change Password</h4>
                                <p class="text-slate-500 text-base mb-4">Ensure your account is using a long, random password to stay secure.</p>
                                
                                <div class="grid grid-cols-1 gap-6">
                                    <div class="flex flex-col">
                                        <label class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2.5">Current Password</label>
                                        <div class="relative flex items-center">
                                            <input :type="showCurrent ? 'text' : 'password'" name="current_password" required placeholder="••••••••" class="w-full pl-5 pr-12 py-3.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-base font-sans">
                                            <button type="button" @click="showCurrent = !showCurrent" class="absolute right-4 p-1.5 rounded-md hover:bg-slate-200/50 transition cursor-pointer">
                                                <i data-lucide="eye" x-show="!showCurrent" class="w-5 h-5 text-slate-400 hover:text-slate-600"></i>
                                                <i data-lucide="eye-off" x-show="showCurrent" class="w-5 h-5 text-slate-400 hover:text-slate-600" style="display: none;"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2.5">New Password</label>
                                        <div class="relative flex items-center">
                                            <input :type="showNew ? 'text' : 'password'" name="new_password" required placeholder="••••••••" class="w-full pl-5 pr-12 py-3.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium text-base font-sans">
                                            <button type="button" @click="showNew = !showNew" class="absolute right-4 p-1.5 rounded-md hover:bg-slate-200/50 transition cursor-pointer">
                                                <i data-lucide="eye" x-show="!showNew" class="w-5 h-5 text-slate-400 hover:text-slate-600"></i>
                                                <i data-lucide="eye-off" x-show="showNew" class="w-5 h-5 text-slate-400 hover:text-slate-600" style="display: none;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex justify-end mt-6">
                                    <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-base shadow-sm transition active:scale-95 cursor-pointer">
                                        Change Password
                                    </button>
                                </div>
                            </div>
                        </form>

                        <hr class="border-slate-100 my-6">

                        <!-- Notifications section -->
                        <div>
                            <h4 class="font-bold text-slate-800 text-lg mb-4">Notification Preferences</h4>
                            
                            <div class="space-y-4">
                                <label class="flex items-center gap-3.5 cursor-pointer">
                                    <input type="checkbox" name="email_notifications" value="1" {{ $emailNotifications ? 'checked' : '' }} form="profileForm" class="w-5 h-5 text-blue-600 border-slate-200 rounded focus:ring-blue-500/20">
                                    <span class="text-base font-medium text-slate-700">Email notifications on new announcements</span>
                                </label>
                                <label class="flex items-center gap-3.5 cursor-pointer">
                                    <input type="checkbox" name="push_notifications" value="1" {{ $pushNotifications ? 'checked' : '' }} form="profileForm" class="w-5 h-5 text-blue-600 border-slate-200 rounded focus:ring-blue-500/20">
                                    <span class="text-base font-medium text-slate-700">Push notifications on evaluation changes</span>
                                </label>
                            </div>
                        </div>

                        <hr class="border-slate-100 my-6">

                        <div class="flex justify-end gap-4">
                            <button type="button" @click="activeTab = 'home'" class="px-6 py-3 border border-slate-200 text-slate-600 rounded-xl font-semibold text-base hover:bg-slate-50 transition active:scale-95 cursor-pointer">Cancel</button>
                            <button type="submit" form="profileForm" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-base shadow-sm transition active:scale-95 cursor-pointer">Save Changes</button>
                        </div>
                    </div>
                </div>
                </div>
            </div>

            <!-- MY IPCRF FORMS TAB -->
            <div x-show="activeTab === 'ipcrf'" x-transition class="space-y-6 w-full" style="display: none;">
                <!-- Header -->
                <div class="mb-2 flex flex-wrap justify-between items-center gap-3">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-800">My IPCRF Forms</h1>
                        <p class="text-sm text-slate-500 mt-1">Digitized IPCRF workflow for your position: <span class="font-semibold text-blue-600">{{ $dbUser?->position?->name ?? 'None Assigned' }}</span></p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="glass-panel p-5 rounded-2xl border border-slate-200/60 flex items-center gap-4">
                        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="file-text" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Assigned</p>
                            <h4 class="text-2xl font-bold text-slate-800" x-text="ipcrfStats.assigned">0</h4>
                        </div>
                    </div>
                    <div class="glass-panel p-5 rounded-2xl border border-slate-200/60 flex items-center gap-4">
                        <div class="w-12 h-12 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="edit-3" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Drafts</p>
                            <h4 class="text-2xl font-bold text-slate-800" x-text="ipcrfStats.drafts">0</h4>
                        </div>
                    </div>
                    <div class="glass-panel p-5 rounded-2xl border border-slate-200/60 flex items-center gap-4">
                        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="clock" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Pending</p>
                            <h4 class="text-2xl font-bold text-slate-800" x-text="ipcrfStats.submitted">0</h4>
                        </div>
                    </div>
                    <div class="glass-panel p-5 rounded-2xl border border-slate-200/60 flex items-center gap-4">
                        <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center">
                            <i data-lucide="check-circle" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Approved</p>
                            <h4 class="text-2xl font-bold text-slate-800" x-text="ipcrfStats.approved">0</h4>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left: Assigned templates -->
                    <div class="lg:col-span-1 glass-panel rounded-2xl shadow-sm p-6 border border-slate-200/60 flex flex-col h-fit">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-4">
                            <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                                <i data-lucide="sparkles" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">Assigned Templates</h3>
                                <p class="text-xs text-slate-500">Templates you can fill out online</p>
                            </div>
                        </div>

                        <div class="space-y-4 max-h-[400px] overflow-y-auto pr-1">
                            <template x-if="assignedTemplates.length === 0">
                                <div class="text-center py-10 text-slate-400">
                                    <i data-lucide="file-x" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                                    <p class="text-sm font-medium">No templates assigned to your position.</p>
                                </div>
                            </template>
                            <template x-for="template in assignedTemplates" :key="template.id">
                                <div class="p-4 bg-slate-50 border border-slate-200/60 rounded-xl hover:border-blue-300 transition-all flex flex-col gap-3">
                                    <div>
                                        <h4 class="font-bold text-slate-800 text-base" x-text="template.name"></h4>
                                        <p class="text-xs text-slate-500 mt-1" x-text="template.description || 'Digitized official IPCRF template'"></p>
                                    </div>
                                    <div class="flex justify-between items-center text-xs text-slate-400">
                                        <span><i class="far fa-list-alt mr-1"></i> <span x-text="template.field_count"></span> fillable fields</span>
                                    </div>
                                    <button @click="startFillForm(template.id)" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl text-sm flex items-center justify-center gap-2 transition active:scale-95 shadow-sm shadow-blue-600/10 cursor-pointer">
                                        <i data-lucide="pen-tool" class="w-4 h-4"></i> Fill Out Online
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Right: Submission history & drafts -->
                    <div class="lg:col-span-2 glass-panel rounded-2xl shadow-sm p-6 border border-slate-200/60 flex flex-col min-h-[300px]">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-4 mb-4">
                            <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center">
                                <i data-lucide="history" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-lg">Submissions & Drafts</h3>
                                <p class="text-xs text-slate-500">Track the status of your online submissions</p>
                            </div>
                        </div>

                        <div class="overflow-x-auto rounded-xl border border-slate-100 flex-1">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 text-xs font-semibold uppercase tracking-wider">
                                        <th class="px-6 py-4">IPCRF Template</th>
                                        <th class="px-6 py-4">Status</th>
                                        <th class="px-6 py-4">Last Updated</th>
                                        <th class="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="submissions.length === 0">
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                                <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-3 opacity-40"></i>
                                                <p class="font-medium text-sm">No submissions or drafts yet.</p>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-for="sub in submissions" :key="sub.id">
                                        <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
                                            <td class="px-6 py-4 font-semibold text-slate-800" x-text="sub.template_name"></td>
                                            <td class="px-6 py-4">
                                                <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider" :class="statusBadgeClass(sub.status)" x-text="sub.status_label"></span>
                                            </td>
                                            <td class="px-6 py-4 text-slate-500" x-text="sub.updated_at"></td>
                                            <td class="px-6 py-4 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <template x-if="sub.status === 'draft'">
                                                        <button @click="startFillForm(sub.template_id)" class="text-blue-600 hover:text-blue-800 font-semibold text-xs flex items-center gap-1 cursor-pointer">
                                                            <i data-lucide="edit-2" class="w-3.5 h-3.5"></i> Edit Draft
                                                        </button>
                                                    </template>
                                                    <template x-if="sub.status === 'approved'">
                                                        <a :href="'/my/submissions/' + sub.id + '/download'" class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-xs font-bold flex items-center gap-1 transition cursor-pointer">
                                                            <i data-lucide="download" class="w-3.5 h-3.5"></i> Download XLSX
                                                        </a>
                                                    </template>
                                                    <template x-if="['submitted', 'under_review', 'rejected'].includes(sub.status)">
                                                        <span class="text-xs text-slate-400 font-medium">Locked (Review)</span>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM FILLING TAB -->
            <div x-show="activeTab === 'fillForm'" x-transition class="space-y-6 w-full h-[calc(100vh-140px)] flex flex-col" style="display: none;">
                <!-- Header Panel -->
                <div class="flex flex-wrap items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-slate-200/60 shadow-sm flex-shrink-0">
                    <div class="flex items-center gap-3">
                        <button @click="exitForm()" class="w-9 h-9 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-lg flex items-center justify-center transition border border-slate-200/40 cursor-pointer">
                            <i data-lucide="arrow-left" class="w-5 h-5"></i>
                        </button>
                        <div>
                            <h2 class="text-xl font-bold text-slate-800" x-text="fillingTemplate ? fillingTemplate.name : ''"></h2>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-xs text-slate-400 font-medium flex items-center gap-1">
                                    <i data-lucide="user" class="w-3.5 h-3.5"></i> Filling as: {{ $name }}
                                </span>
                                <span class="text-xs text-slate-300">•</span>
                                <span class="text-xs font-semibold text-indigo-600 flex items-center gap-1" x-show="savingStatus">
                                    <i class="fas fa-spinner fa-spin" x-show="savingStatus.includes('Saving')"></i>
                                    <i data-lucide="cloud-check" class="w-3.5 h-3.5" x-show="savingStatus.includes('saved')"></i>
                                    <span x-text="savingStatus"></span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="saveDraft(false)" class="px-5 py-2.5 border border-slate-200 text-slate-600 rounded-xl font-bold text-sm hover:bg-slate-50 transition active:scale-95 cursor-pointer flex items-center gap-2">
                            <i data-lucide="save" class="w-4 h-4"></i> Save Draft
                        </button>
                        <button @click="submitForm()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm transition active:scale-95 shadow-sm shadow-blue-600/10 cursor-pointer flex items-center gap-2">
                            <i data-lucide="send" class="w-4 h-4"></i> Submit Final IPCRF
                        </button>
                    </div>
                </div>

                <!-- Grid Sheet Content Area -->
                <div class="flex-1 ipcrf-table-wrapper" x-html="formHtml"></div>
            </div>
            
            <!-- LOADING OVERLAY -->
            <div x-show="loadingIpcrf" x-transition class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-50" style="display: none;">
                <div class="bg-white p-6 rounded-2xl shadow-xl flex items-center gap-3">
                    <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
                    <span class="font-bold text-slate-800 text-base">Processing Form...</span>
                </div>
            </div>
            
        </div>
        </main>
    </div>

    <!-- LOGOUT CONFIRMATION MODAL -->
    <div x-show="showLogoutModal"
         x-transition
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50"
         style="display: none;">

        <div class="bg-white rounded-xl shadow-2xl p-6 w-96 text-center border border-slate-100">
            <div class="w-12 h-12 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="log-out" class="w-6 h-6"></i>
            </div>
            <h3 class="text-lg font-semibold mb-2 text-slate-800">Confirm Logout</h3>
            <p class="text-sm text-slate-500 mb-6">Are you sure you want to sign out?</p>

            <div class="flex gap-3 justify-center">
                <button @click="showLogoutModal = false"
                        class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition font-medium cursor-pointer">
                    Cancel
                </button>

                <button @click="
                    showLogoutModal = false;
                    $refs.logoutForm.submit();
                "
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium transition cursor-pointer">
                    Logout
                </button>
            </div>
        </div>
    </div>

    <!-- ADD EMAIL MODAL -->
    <div x-show="showAddEmailModal"
         x-transition
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 animate-fade-in"
         style="display: none;">

        <div class="bg-white rounded-xl shadow-2xl p-6 w-96 text-left border border-slate-100" @click.away="showAddEmailModal = false">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mb-4">
                <i data-lucide="mail" class="w-6 h-6"></i>
            </div>
            <h3 class="text-lg font-bold mb-2 text-slate-800">Add Email Address</h3>
            <p class="text-sm text-slate-500 mb-4">Please enter the secondary email address you would like to add.</p>

            <div class="flex flex-col mb-6">
                <input type="email" x-model="newEmail" placeholder="e.g. secondary@example.com" @keydown.enter="addEmail(newEmail)"
                       class="w-full px-4 py-3 rounded-lg border border-slate-200 bg-slate-50 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium">
            </div>

            <div class="flex gap-3 justify-end">
                <button type="button" @click="showAddEmailModal = false; newEmail = '';"
                        class="px-4 py-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition font-medium cursor-pointer">
                    Cancel
                </button>

                <button type="button" @click="addEmail(newEmail)"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition cursor-pointer">
                    Add Email
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            lucide.createIcons();
        });

        function confirmProfileUpdate(event) {
            @if(!$profileEdited)
                const confirmed = confirm("Please check your information because you can only edit it one time. Are you sure you want to save changes?");
                if (!confirmed) {
                    event.preventDefault();
                    return false;
                }
            @endif
            return true;
        }

        function initSpreadsheetResizers() {
            // Column Resizers
            document.querySelectorAll('.col-resizer').forEach((resizer) => {
                resizer.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    resizer.classList.add('dragging');
                    const startX = e.clientX;
                    const thEl = resizer.closest('.ipcrf-hdr-col');
                    if (!thEl) return;

                    const colIdx = parseInt(thEl.getAttribute('data-col-idx'));

                    // Retrieve the corresponding <col> in colgroup
                    // In our table parser we added 1 col at the start, so colIndex in colgroup is colIdx + 1
                    const colEl = document.querySelector(`.ipcrf-preview-table colgroup col:nth-child(${colIdx + 1})`);
                    if (!colEl) return;

                    const startWidth = colEl.getBoundingClientRect().width || 80;

                    const onMouseMove = (moveEvent) => {
                        const dx = moveEvent.clientX - startX;
                        const newWidth = Math.max(30, startWidth + dx);
                        colEl.style.width = newWidth + 'px';
                    };

                    const onMouseUp = () => {
                        resizer.classList.remove('dragging');
                        document.removeEventListener('mousemove', onMouseMove);
                        document.removeEventListener('mouseup', onMouseUp);
                    };

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });
            });

            // Row Resizers
            document.querySelectorAll('.row-resizer').forEach((resizer) => {
                resizer.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    resizer.classList.add('dragging');
                    const startY = e.clientY;
                    const trEl = resizer.closest('tr');
                    if (!trEl) return;

                    const startHeight = trEl.getBoundingClientRect().height;

                    const onMouseMove = (moveEvent) => {
                        const dy = moveEvent.clientY - startY;
                        const newHeight = Math.max(18, startHeight + dy);
                        trEl.style.height = newHeight + 'px';
                    };

                    const onMouseUp = () => {
                        resizer.classList.remove('dragging');
                        document.removeEventListener('mousemove', onMouseMove);
                        document.removeEventListener('mouseup', onMouseUp);
                    };

                    document.addEventListener('mousemove', onMouseMove);
                    document.addEventListener('mouseup', onMouseUp);
                });
            });
        }
    </script>
</body>
</html>