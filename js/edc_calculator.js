
    function calculateEDC() {
        const lmpInput = document.getElementById("lmp");
        const edcInput = document.getElementById("edc");

        // Get the value of the LMP
        const lmpDate = new Date(lmpInput.value);

        if (!isNaN(lmpDate)) {
            // Add 280 days (40 weeks) to the LMP
            const edcDate = new Date(lmpDate);
            edcDate.setDate(edcDate.getDate() + 280);

            // Format the EDC date to YYYY-MM-DD for input
            const formattedEDC = edcDate.toISOString().split("T")[0];

            // Set the calculated EDC value
            edcInput.value = formattedEDC;
        } else {
            edcInput.value = ""; // Clear EDC if LMP is invalid
        }
    }
