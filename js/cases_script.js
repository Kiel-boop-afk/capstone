function fetchRecordId() {
    var patientId = document.getElementById("patient_id").value;
    var recordIdField = document.getElementById("record_id");

    // Clear the current record ID
    recordIdField.value = "";

    // If no patient is selected, exit
    if (!patientId) {
        return;
    }

    // AJAX request to fetch the active record ID
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "fetch_active_record.php?patient_id=" + encodeURIComponent(patientId), true);

    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);

            if (response && response.record_id) {
                recordIdField.value = response.record_id;
            } else {
                recordIdField.value = "No active record";
            }
        }
    };

    xhr.send();
}

    // Function to open the first popup form (add form)
    function openForm1() {
        document.getElementById("popupForm1").style.display = "flex";
    }

    // Function to close the first popup form (add form)
    function closeForm1() {
        document.getElementById("popupForm1").style.display = "none";
    }

    // Function to open the popup form (Update Form)
    function openForm2() {
        document.getElementById("popupForm2").style.display = "flex";
    }

    // Function to close the popup form (Update Form)
    function closeForm2() {
        document.getElementById("popupForm2").style.display = "none";
    }

    // Function to open the popup form (Search Form)
    function openForm3() {
        document.getElementById("popupForm3").style.display = "flex";
    }

    // Function to close the popup form (Search Form)
    function closeForm3() {
        document.getElementById("popupForm3").style.display = "none";
    }

    // Function to open the popup form (Discharge Form)
    function openForm4() {
        document.getElementById("popupForm4").style.display = "flex";
    }

    // Function to close the popup form (Discharge Form)
    function closeForm4() {
        document.getElementById("popupForm4").style.display = "none";
    }

