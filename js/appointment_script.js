  // Function to open the first popup form (add form)
    function openForm1() {
        document.getElementById("popupForm1").style.display = "flex";
    }

    // Function to close the first popup form (add form)
    function closeForm1() {
        document.getElementById("popupForm1").style.display = "none";
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

// Assuming you have the values for appointment_id, patient_id, and patient_name 
// when the user clicks the "Reschedule" button

document.querySelectorAll('.reschedule-appointment').forEach(button => {
    button.addEventListener('click', function() {
        // Get data attributes from the clicked button
        const appointmentId = this.getAttribute('data-appointment-id');
        const patientId = this.getAttribute('data-patient-id');
        const patientName = this.getAttribute('data-patient-name');
        const reason = this.getAttribute('data-reason');

        // Set values in the form
        document.getElementById('appointmentId').value = appointmentId;
        document.getElementById('patientId').value = patientId;
        document.getElementById('patientName').value = patientName;
        document.getElementById('reason').value = reason;

        // Display the values in the read-only fields
        document.getElementById('appointmentIdDisplay').value = appointmentId;
        document.getElementById('patientIdDisplay').value = patientId;
        document.getElementById('patientNameDisplay').value = patientName;

        // Show the reschedule form
        document.getElementById('rescheduleForm').style.display = 'block';
        document.getElementById('overlay').style.display = 'block'; // If using overlay
    });
});

// Close the form or hide it
document.getElementById('cancelReschedule').addEventListener('click', function() {
    document.getElementById('rescheduleForm').style.display = 'none';
    document.getElementById('overlay').style.display = 'none'; // If using overlay
});

 // Function to filter the patients list based on the search input
    function filterPatients() {
        const searchTerm = document.getElementById('patient_search').value.toLowerCase();
        const patientList = document.getElementById('patient_list');
        const items = patientList.getElementsByClassName('dropdown-item');
        
        // Loop through the items and hide those that do not match the search term
        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            const text = item.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                item.style.display = 'block'; // Show matching items
            } else {
                item.style.display = 'none'; // Hide non-matching items
            }
        }
    }

    // Event listener for item selection from the dropdown
    document.getElementById('patient_list').addEventListener('click', function(event) {
        if (event.target.classList.contains('dropdown-item')) {
            const selectedPatientName = event.target.getAttribute('data-name');
            const selectedPatientId = event.target.getAttribute('data-id');
            
            // Set the selected patient name and ID in the input field
            document.getElementById('patient_search').value = selectedPatientName + " (ID: " + selectedPatientId + ")";
            // Set the hidden patient ID field
            document.getElementById('patient_id').value = selectedPatientId;
            
            // Close the dropdown list after selection
            document.getElementById('patient_list').style.display = 'none';
        }
    });

    // Hide the dropdown list when clicking outside of it
    document.addEventListener('click', function(event) {
        const dropdownContainer = document.querySelector('.dropdown-container');
        const patientList = document.getElementById('patient_list');
        if (!dropdownContainer.contains(event.target)) {
            patientList.style.display = 'none';
        }
    });

    // Show the dropdown list when the user focuses on the input field
    document.getElementById('patient_search').addEventListener('focus', function() {
        document.getElementById('patient_list').style.display = 'block';
    });

// Handle "DONE" button click
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('done-appointment')) {
        const appointmentId = event.target.getAttribute('data-appointment-id');
        const patientId = event.target.getAttribute('data-patient-id');
        const reason = event.target.getAttribute('data-reason');
        
        // Check if the reason is "first-pregnancy-checkup" and open the maternity record popup
        if (reason === 'first-pregnancy-checkup') {
            document.getElementById('maternity_patient_id').value = patientId;
            document.getElementById('maternity_appointment_id').value = appointmentId;
        
            // Fetch Gravida, Para, and Abortions from the database
            fetch('fetch_patient_gpa.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `patient_id=${encodeURIComponent(patientId)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(data.error); // Handle errors
                } else {
                    // Populate the fields with the fetched data
                    document.getElementById('gravida').value = data.gravida || '';
                    document.getElementById('para').value = data.para || '';
                    document.getElementById('abortions').value = data.abortus || '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching patient data.');
            });
        
            // Display the popup
            document.getElementById('maternityRecordPopup').style.display = 'flex';
        }
        
        
        // Check if the reason is "TT" and open the TT popup
        if (reason === 'TT') {
            document.getElementById('tt_patient_id').value = patientId;
            document.getElementById('tt_appointment_id').value = appointmentId;


            document.getElementById('ttPopupForm').style.display = 'flex';
        }

        // Check if the reason is "Antepartum Visit" and open the Antepartum Visit form
        if (reason === 'Antepartumvisit' || reason === "first-pregnancy-checkup-Antepartumvisit") {
            // Populate the Antepartum Visit form with the appointment and patient details
            
            // Set the patient_id and appointment_id values in the hidden inputs of the Antepartum Visit form
            document.getElementById('av_patient_id').value = patientId;
            document.getElementById('av_appointment_id').value = appointmentId;

            // Show the Antepartum Visit form popup
            document.getElementById('antepartumVisitForm').style.display = 'flex';
        }
        // Dental Checkup
        if (reason === 'Dental-Checkup') {
            document.getElementById('dental_patient_id').value = patientId;
            document.getElementById('dental_appointment_id').value = appointmentId;

            document.getElementById('dentalCheckupForm').style.display = 'flex';
        }
    }
});

// Attach event listeners to all radio buttons
document.querySelectorAll('input[name="reasonOption"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        // Update the textbox with the selected radio button's value
        document.getElementById('reason').value = this.value;
    });
});



// Close the popup form
function closePopup1() {
    document.getElementById('maternityRecordPopup').style.display = 'none';
}

function closePopup2() {
    document.getElementById('ttPopupForm').style.display = 'none';
}

function closeAntepartumForm() {
    document.getElementById('antepartumVisitForm').style.display = 'none';
}

function closeDentalForm() {
    document.getElementById('dentalCheckupForm').style.display = 'none';
}
