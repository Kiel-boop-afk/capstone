
    // Function to open the first popup form (add form)
    function openForm1() {
        document.getElementById("popupForm1").style.display = "flex";
    }

    // Function to close the first popup form (add form)
    function closeForm1() {
        document.getElementById("popupForm1").style.display = "none";
    }

    // Function to open the second popup form (Update Form)
    function openForm2() {
        document.getElementById("popupForm2").style.display = "flex";
    }

    // Function to close the second popup form (Update Form)
    function closeForm2() {
        document.getElementById("popupForm2").style.display = "none";
    }

    // Function to open the fourth popup form (Refer form)
    function openForm4() {
        document.getElementById("popupForm4").style.display = "flex";
    }

    // Function to close the fourth popup form (Refer form)
    function closeForm4() {
        document.getElementById("popupForm4").style.display = "none";
    }
    
    
    // Function to toggle dropdown visibility based on selected option
    function toggleDropdown(selectedDropdown) {
        // Hide all dropdowns
        document.getElementById('clinicDropdown').style.display = 'none';
        document.getElementById('doctorDropdown').style.display = 'none';
        document.getElementById('hospitalDropdown').style.display = 'none';

        // Show the selected dropdown
        document.getElementById(selectedDropdown).style.display = 'block';
    }

  // Variable to store the field to be unlocked
var unlockedField = '';

// Function to open the popup when a locked field is clicked
function openUnlockPopup(fieldName) {
    var field = document.getElementById(fieldName);
    
    // Check if the field is locked (readonly)
    if (field.hasAttribute('readonly')) {
        // Store the field to be unlocked in a variable
        unlockedField = fieldName;

        // Enable the unlock_key field
        var unlockKeyField = document.getElementById('unlock_key');
        unlockKeyField.disabled = false;

        // Show the popup
        document.getElementById('popup-key').style.display = 'block';
    }
}

// Function to close the popup without unlocking the field
function closeUnlockPopup() {
    // Hide the popup
    document.getElementById('popup-key').style.display = 'none';

    // Clear and disable the unlock_key field
    var unlockKeyField = document.getElementById('unlock_key');
    unlockKeyField.value = '';
    unlockKeyField.disabled = true;
}

// Function to unlock the fields if the correct key is entered
function unlockFields() {
    var unlockKeyField = document.getElementById('unlock_key');
    var key = unlockKeyField.value;
    // Replace this with the actual key validation logic
    var correctKey = '123'; // Example key, replace with actual logic

    if (key === correctKey) {
        // Unlock the field
        document.getElementById(unlockedField).removeAttribute('readonly');
        // Close the popup
        closeUnlockPopup();
    } else {
        alert('Incorrect key. Please try again.');
    }
}

// Perform search based on user input
function performSearch() {
    const query = document.querySelector('.search-bar').value.toLowerCase();
    const rows = document.querySelectorAll('.searchable-item'); // Target rows with 'searchable-item' class
    
    rows.forEach(row => {
        const rowText = row.textContent.toLowerCase(); // Get the text content of the row
        if (rowText.includes(query)) {
            row.style.display = ''; // Show the row if it matches the search
        } else {
            row.style.display = 'none'; // Hide the row if it doesn't match
        }
    });
}


document.addEventListener("DOMContentLoaded", () => {
    const popup = document.getElementById("patientPopup");
    const closeBtn = document.querySelector(".popup-close");

    // Initially hide the popup on page load
    popup.style.display = "none";

    // Add event listener for each "View" button
    document.querySelectorAll(".view-patient").forEach(button => {
        button.addEventListener("click", async () => {
            const patientId = button.dataset.patientId;

            try {
                // Fetch patient details via PHP
                const response = await fetch(`get_patient_details.php?patient_id=${patientId}`);
                if (!response.ok) {
                    throw new Error("Failed to fetch patient details.");
                }

                const patient = await response.json();

                // Populate popup fields with patient data, or set default "N/A" if not available
                document.getElementById("popup-patient-id").textContent = patient.patient_id || "N/A";
                document.getElementById("popup-name").textContent = patient.name || "N/A";
                document.getElementById("popup-address").textContent = patient.address || "N/A";
                document.getElementById("popup-currently-at").textContent = patient.currently_at || "N/A";
                document.getElementById("popup-phone").textContent = patient.phone || "N/A";
                document.getElementById("popup-occupation").textContent = patient.occupation || "N/A";
                document.getElementById("popup-birthday").textContent = patient.birthday || "N/A";
                document.getElementById("popup-height").textContent = patient.height || "N/A";
                document.getElementById("popup-husband").textContent = patient.husband || "N/A";
                document.getElementById("popup-husband-occupation").textContent = patient.husband_occupation || "N/A";
                document.getElementById("popup-philhealth-number").textContent = patient.philhealth_number || "N/A";

                 // Add gravidity, para, abortus to the popup
                document.getElementById("popup-gravida").textContent = patient.gravida || "N/A";
                document.getElementById("popup-para").textContent = patient.para || "N/A";
                document.getElementById("popup-abortus").textContent = patient.abortus || "N/A";


                // Calculate age if birthday is available
                if (patient.birthday) {
                    const birthday = new Date(patient.birthday);
                    const today = new Date();
                    const age = today.getFullYear() - birthday.getFullYear() - 
                        (today < new Date(today.getFullYear(), birthday.getMonth(), birthday.getDate()) ? 1 : 0);
                    document.getElementById("popup-age").textContent = age;
                } else {
                    document.getElementById("popup-age").textContent = "N/A";
                }

                // Show the popup
                popup.style.display = "flex"; // Use flex to center the popup
            } catch (error) {
                console.error("Error fetching patient details:", error);
                alert("Could not load patient details. Please try again later.");
            }
        });
    });

    // Close the popup when the close button is clicked
    closeBtn.addEventListener("click", closePopup);

    // Close the popup when clicking outside of the popup content
    window.addEventListener("click", (e) => {
        if (e.target === popup) {
            popup.style.display = "none";
        }
    });
});
function closePopup() {
    const popup = document.getElementById("patientPopup");
    popup.style.display = "none"; // Hide the popup when the close button is clicked
}

document.addEventListener("DOMContentLoaded", () => {
    const maternityPopup = document.getElementById("maternityPopup");
    const closeBtn = maternityPopup.querySelector(".popup-close");

    // Hide the popup initially
    maternityPopup.style.display = "none";

    // Function to fetch maternity records and display them in a table within the popup
    async function fetchMaternityRecords(patientId) {
        try {
            // Fetch maternity records for the patient
            const response = await fetch(`get_maternity_records.php?patient_id=${patientId}`);
            if (!response.ok) {
                throw new Error("Failed to fetch maternity records.");
            }

            const records = await response.json();

            if (records.length > 0) {
                // Construct the table structure for the maternity records
                let tableHtml = `
                    <table>
                        <thead>
                            <tr>
                                <th>Record ID</th>
                                <th>Patient ID</th>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                // Loop through each record and add it to the table
                records.forEach(record => {
                    tableHtml += `
                        <tr>
                            <td>${record.record_id}</td>
                            <td>${record.patient_id}</td>
                            <td>${record.name}</td>
                            <td><button class="expand-btn" data-record-id="${record.record_id}">Expand</button></td>
                        </tr>
                    `;
                });
                tableHtml += `</tbody></table>`;

                // Insert the generated table into the popup content
                document.getElementById("maternity-records-list").innerHTML = tableHtml;

                // Show the popup
                maternityPopup.style.display = "flex";
            } else {
                alert("No maternity records found for this patient.");
            }
        } catch (error) {
            console.error("Error fetching maternity records:", error);
            alert("Could not load maternity records. Please try again later.");
        }
    }

    // Handle the "VIEW" button click to open the popup with the records table
    document.addEventListener("click", (event) => {
        if (event.target.classList.contains("view-maternity-record")) {
            const patientId = event.target.dataset.patientId;
            fetchMaternityRecords(patientId);
        }

        // Expand the maternity record when "Expand" button is clicked
        if (event.target.classList.contains("expand-btn")) {
            const recordId = event.target.dataset.recordId;
            expandRecord(recordId);
        }
    });

    // Function to fetch and display additional details for a single record
    async function expandRecord(recordId) {
        try {
            const response = await fetch(`get_maternity_record_details.php?record_id=${recordId}`);
            if (!response.ok) {
                throw new Error("Failed to fetch record details.");
            }

            const recordDetails = await response.json();
            if (recordDetails) {
                let expandedHtml = `
                    <h4>Expanded Details for Record ID: ${recordDetails.record_id}</h4>
                    <p><strong>Patient ID:</strong> ${recordDetails.patient_id}</p>
                    <p><strong>Name:</strong> ${recordDetails.name}</p>
                    <p><strong>Admission Date:</strong> ${recordDetails.date_and_time_of_admission}</p>
                    <p><strong>Diagnosis:</strong> ${recordDetails.admitting_diagnosis}</p>
                    <p><strong>Outcome of Delivery:</strong> ${recordDetails.outcome_of_delivery}</p>
                `;

                // Append the expanded details inside the table row or a modal-like view
                const expandButton = document.querySelector(`[data-record-id="${recordId}"]`);
                const parentRow = expandButton.closest("tr");
                const expandedRow = document.createElement("tr");
                expandedRow.innerHTML = `<td colspan="4">${expandedHtml}</td>`;
                parentRow.after(expandedRow);
                expandButton.disabled = true; // Disable the "Expand" button after it's clicked
            } else {
                alert("Could not fetch details for this record.");
            }
        } catch (error) {
            console.error("Error fetching record details:", error);
            alert("Could not load record details. Please try again later.");
        }
    }

    // Close the maternity records popup
    closeBtn.addEventListener("click", () => {
        maternityPopup.style.display = "none";
    });

    // Close the popup if clicking outside the popup content
    window.addEventListener("click", (e) => {
        if (e.target === maternityPopup) {
            maternityPopup.style.display = "none";
        }
    });
});

// Validate Gravida (G), Para (P), and Abortus (A) values
function validateGPA() {
    const gravida = parseInt(document.getElementById("gravida").value) || 0;
    const para = parseInt(document.getElementById("para").value) || 0;
    const abortus = parseInt(document.getElementById("abortus").value) || 0;

    // Ensure Para + Abortus combined doesn't exceed Gravida
    if ((para + abortus) > gravida) {
        alert("The combined value of Para (P) and Abortus (A) cannot exceed Gravida (G). Please adjust the values.");
        return false; // Prevent form submission
    }

    return true; // Allow form submission if validation passes
}

