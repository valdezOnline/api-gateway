<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Test</title>

    @vite(['resources/css/app.css'])
</head>

<body class="antialiased" onload="updateDateExceptions();">
    <button id="previous">Previous</button>
    <button id="next">Next</button>

    <span id="start"></span> to <span id="end"></span>

    <!-- Rivera -->
    <table border="1" cellpadding="1" cellspacing="1" id="rivera-hours" style="width: 100%;display:none">
        <thead>
            <tr>
                <td>&nbsp;</td>
                <td>SUN</td>
                <td>MON</td>
                <td>TUES</td>
                <td>WEDS</td>
                <td>THURS</td>
                <td>FRI</td>
                <td>SAT</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Tomás Rivera Library</strong></td>
                <td>1pm - 9pm</td>
                <td>7:30am - 12:00am</td>
                <td>7:30am - 12:00am</td>
                <td>7:30am - 12:00am</td>
                <td>7:30am - 12:00am</td>
                <td>7:30am - 6:00pm</td>
                <td>9:00am - 5:00pm</td>
            </tr>
            <tr>
                <td><strong>Library Administration</strong></td>
                <td>Closed</td>
                <td>
                    <p>9:00am - 5:00pm</p>

                    <p>Closed 12 noon - 1:00pm</p>
                </td>
                <td>
                    <p>9:00am - 5:00pm</p>

                    <p>Closed 12 noon - 1:00pm</p>
                </td>
                <td>
                    <p>9:00am - 5:00pm</p>

                    <p>Closed 12 noon - 1:00pm</p>
                </td>
                <td>
                    <p>9:00am - 5:00pm</p>

                    <p>Closed 12 noon - 1:00pm</p>
                </td>
                <td>
                    <p>9:00am - 5:00pm</p>

<<<<<<< HEAD
                    <p>Closed 12 noon - 1:00pm</p>
                </td>
                <td>Closed</td>
            </tr>
            <tr>
                <td><strong>Library Support/InfoDesk</strong></td>
                <td>Closed</td>
                <td>9:00am - 5:00pm</td>
                <td>9:00am - 5:00pm</td>
                <td>9:00am - 5:00pm</td>
                <td>9:00am - 5:00pm</td>
                <td>9:00am - 5:00pm</td>
                <td>Closed</td>
            </tr>
            <tr>
                <td><strong>IT Support/BearHelp</strong></td>
                <td>Closed</td>
                <td>8:30am – 4:30pm</td>
                <td>8:30am – 4:30pm</td>
                <td>8:30am – 4:30pm</td>
                <td>8:30am – 4:30pm</td>
                <td>8:30am – 4:30pm</td>
                <td>Closed</td>
            </tr>
        </tbody>
    </table>

    <p>&nbsp;</p>

    <!-- Orbach -->
    <table border="1" cellpadding="1" cellspacing="1" id="orbach-hours" style="width: 100%;display:none">
        <thead>
            <tr>
                <td>&nbsp;</td>
                <td>SUN</td>
                <td>MON</td>
                <td>TUES</td>
                <td>WEDS</td>
                <td>THURS</td>
                <td>FRI</td>
                <td>SAT</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Orbach Science Library</strong></td>
                <td>1pm - 9pm</td>
                <td>7:30am - 12:00am</td>
                <td>7:30am - 12:00am</td>
                <td>7:30am - 12:00am</td>
                <td>7:30am - 12:00am</td>
                <td>7:30am - 6:00pm</td>
                <td>9:00am - 5:00pm</td>
            </tr>
            <tr>
                <td><strong>Library Support/InfoDesk</strong></td>
                <td>Closed</td>
                <td>9:00am - 5:00pm</td>
                <td>9:00am - 5:00pm</td>
                <td>9:00am - 5:00pm</td>
                <td>9:00am - 5:00pm</td>
                <td>9:00am - 5:00pm</td>
                <td>Closed</td>
            </tr>
            <tr>
                <td><strong>Creat'R Lab</strong></td>
                <td>Closed</td>
                <td>11:00am - 5:00pm</td>
                <td>11:00am - 5:00pm</td>
                <td>11:00am - 5:00pm</td>
                <td>11:00am - 5:00pm</td>
                <td>11:00am - 5:00pm</td>
                <td>Closed</td>
            </tr>
            <tr>
                <td><strong>IT Support/BearHelp</strong></td>
                <td>Closed</td>
                <td>8:30am – 4:30pm</td>
                <td>8:30am – 4:30pm</td>
                <td>8:30am – 4:30pm</td>
                <td>8:30am – 4:30pm</td>
                <td>8:30am – 4:30pm</td>
                <td>Closed</td>
            </tr>
        </tbody>
    </table>

    <p>&nbsp;</p>


    <!-- SCUA -->
    <table border="1" cellpadding="1" cellspacing="1" id="scua-hours" style="width: 100%;display:none">
        <thead>
            <tr>
                <td>&nbsp;</td>
                <td>SUN</td>
                <td>MON</td>
                <td>TUES</td>
                <td>WEDS</td>
                <td>THURS</td>
                <td>FRI</td>
                <td>SAT</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Special Collections &amp; University Archives</td>
                <td>Closed</td>
                <td>10:00am - 4:00pm</td>
                <td>10:00am - 4:00pm</td>
                <td>10:00am - 4:00pm</td>
                <td>10:00am - 4:00pm</td>
                <td>10:00am - 4:00pm</td>
                <td>Closed</td>
            </tr>
        </tbody>
    </table>

    <p>&nbsp;</p>

    {{-- <script type="text/javascript">
        var selectedDate = new Date();

        function fillCalendarDays() {
            const today = new Date(selectedDate);
            const days = [];
            const names = ['SUN', 'MON', 'TUES', 'WEDS', 'THURS', 'FRI', 'SAT'];

            // Get the days of the requested week
            for (var i = 0; i < 7; i++) {
                days.push(new Date(today.setDate(today.getDate() - today.getDay() + i)));
                var xpath = "//td[text()[contains(.,'" + names[i] + "')]]";
                var result = document.evaluate(xpath, document, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);

                for (var j = 0; j < result.snapshotLength; j++) {
                    var node = result.snapshotItem(j);
                    node.innerHTML = names[i] + " " + days[i].getDate();
=======
            <p>Closed 12 noon - 1:00pm</p>
        </td>
        <td>Closed</td>
    </tr>
    <tr>
        <td><strong>Information Desk Rivera</strong></td>
        <td>Closed</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>Closed</td>
    </tr>
    <tr>
        <td><strong>IT Support/Helpdesk</strong></td>
        <td>Closed</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>Closed</td>
    </tr>
    </tbody>
</table>

<table border="1" cellpadding="1" cellspacing="1" id="scua-hours" style="width: 100%">
    <thead>
    <tr>
        <td>&nbsp;</td>
        <td>SUN</td>
        <td>MON</td>
        <td>TUES</td>
        <td>WEDS</td>
        <td>THURS</td>
        <td>FRI</td>
        <td>SAT</td>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><strong>Special Collections & University Archives</strong></td>
        <td>Closed</td>
        <td>7:30am - 8:00pm</td>
        <td>7:30am - 8:00pm</td>
        <td>7:30am - 8:00pm</td>
        <td>7:30am - 8:00pm</td>
        <td>7:30am - 8:00pm</td>
        <td>9:00am - 5:00pm</td>
    </tr>

    </tbody>
</table>

<table border="1" cellpadding="1" cellspacing="1" id="orbach-hours" style="width: 100%">
    <thead>
    <tr>
        <td>&nbsp;</td>
        <td>SUN</td>
        <td>MON</td>
        <td>TUES</td>
        <td>WEDS</td>
        <td>THURS</td>
        <td>FRI</td>
        <td>SAT</td>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><strong>Orbach Science Library</strong></td>
        <td>Closed</td>
        <td>7:30am - 8:00pm</td>
        <td>7:30am - 8:00pm</td>
        <td>7:30am - 8:00pm</td>
        <td>7:30am - 8:00pm</td>
        <td>7:30am - 8:00pm</td>
        <td>9:00am - 5:00pm</td>
    </tr>
    <tr>
        <td><strong>Library Support/InfoDesk</strong></td>
        <td>Closed</td>
        <td>
            <p>9:00am - 5:00pm</p>

            <p>Closed 12 noon - 1:00pm</p>
        </td>
        <td>
            <p>9:00am - 5:00pm</p>

            <p>Closed 12 noon - 1:00pm</p>
        </td>
        <td>
            <p>9:00am - 5:00pm</p>

            <p>Closed 12 noon - 1:00pm</p>
        </td>
        <td>
            <p>9:00am - 5:00pm</p>

            <p>Closed 12 noon - 1:00pm</p>
        </td>
        <td>
            <p>9:00am - 5:00pm</p>

            <p>Closed 12 noon - 1:00pm</p>
        </td>
        <td>Closed</td>
    </tr>
    <tr>
        <td><strong>Creat'R Lab</strong></td>
        <td>Closed</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>Closed</td>
    </tr>
    <tr>
        <td><strong>IT Support/BearHelp</strong></td>
        <td>Closed</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>10:00am - 2:00pm</td>
        <td>Closed</td>
    </tr>
    </tbody>
</table>
<script type="text/javascript" defer>
    // Declare script-wide variables
    var oldValues = {};
    var locations = [
        "All locations",
        "Tomás Rivera Library",
        "Library Administration",
        "Library Support/InfoDesk - Rivera",
        "IT Support/BearHelp - Rivera",
        "Orbach Science Library",
        "Library Support/InfoDesk",
        "Creat'R Lab",
        "IT Support/BearHelp - Orbach",
        "Special Collections & University Archives"
    ];
    var tables = document.querySelectorAll('table');
    var selectedDate = new Date();

    // Fills the header with the Dates.
    function fillCalendarDays() {
        const today = new Date(selectedDate);
        const month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const days = [];
        const names = ['SUN', 'MON', 'TUES', 'WEDS', 'THURS', 'FRI', 'SAT'];

        // Get the days of the requested week
        for (var i = 0; i < 7; i++) {
            days.push(new Date(today.setDate(today.getDate() - today.getDay() + i)));

            var xpath = "//td[text()[contains(.,'" + names[i] + "')]]";
            var result = document.evaluate(xpath, document, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);

            for (var j = 0; j < result.snapshotLength; j++) {
                var node = result.snapshotItem(j);
                //console.log((days[i]).getMonth())
                node.innerHTML = names[i] + " - " + month[(days[i]).getMonth()] + " " + days[i].getDate();
            }
        }

        try {
            document.getElementById('start').innerText = days[0].toLocaleDateString();
            document.getElementById('end').innerText = days[6].toLocaleDateString();
        } catch (error) {
            console.log(error);
        }
    }

    function nextWeek() {
        selectedDate.setDate(selectedDate.getDate() + 7);
        fillCalendarDays();
        updateDateExceptions();
    }

    function previousWeek() {
        selectedDate.setDate(selectedDate.getDate() - 7);
        fillCalendarDays();
        updateDateExceptions();
    }

    //fillCalendarDays();

    document.querySelector('#next').addEventListener('click', nextWeek)
    document.querySelector('#previous').addEventListener('click', previousWeek)

    ////////////////////////////////////////////////////////////
    // Save the old date values so we can rewrite them as needed
    function saveAndClearDefaultHours() {
        // Save default values and clear/empty the display
        tables.forEach(function(table) {
            var tBody = table.getElementsByTagName('tbody')[0];
            oldValues[table.id] = tBody.innerHTML;
            tBody.innerHTML = "";
        });
    }

    // Function called inside fillExceptions()...
    function fillRow(start, end, row, exceptions) {
        loc = row.cells[0].innerText;

        // Find any exceptions for this location
        exceptions.forEach(function(exception) {
            dateIndex = new Date(start);

            if (exception.location == 0 || locations[exception.location] == loc) {
                // Loop through the row cells and set exceptions for any days as needed
                exceptionStart = new Date(exception.started_at);
                exceptionEnd = new Date(exception.ended_at);

                for (var i = 1; i < row.cells.length; i++) {
                    // Check if the exception falls on this date
                    if (dateIndex >= exceptionStart &&
                        dateIndex <= exceptionEnd) {
                        row.cells[i].innerText = exception.description;
                    }

                    // Increment the date index
                    dateIndex.setDate(dateIndex.getDate() + 1);
>>>>>>> main
                }
            }

<<<<<<< HEAD
            try {
                document.getElementById('start').innerText = days[0].toLocaleDateString();
                document.getElementById('end').innerText = days[6].toLocaleDateString();
            } catch (error) {
                console.log(error);
            }
        }

        function nextWeek() {
            selectedDate.setDate(selectedDate.getDate() + 7);
            fillCalendarDays();
        }

        function previousWeek() {
            selectedDate.setDate(selectedDate.getDate() - 7);
            fillCalendarDays();
        }

        fillCalendarDays();

        document.querySelector('#next').addEventListener('click', nextWeek)
        document.querySelector('#previous').addEventListener('click', previousWeek)
    </script> --}}

    <script type="text/javascript" defer>
        // Declare script-wide variables
        var oldValues = {};
        var locations = [
            "All locations",
            "Tomás Rivera Library",
            "Library Administration",
            "Library Support/InfoDesk - Rivera",
            "IT Support/BearHelp - Rivera",
            "Orbach Science Library",
            "Library Support/InfoDesk",
            "Creat'R Lab",
            "IT Support/BearHelp - Orbach",
            "Special Collections & University Archives"
        ];
        var tables = document.querySelectorAll('table');
        var selectedDate = new Date();

        // Fills the header with the Dates.
        function fillCalendarDays() {
            const today = new Date(selectedDate);
            const month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const days = [];
            const names = ['SUN', 'MON', 'TUES', 'WEDS', 'THURS', 'FRI', 'SAT'];

            // Get the days of the requested week
            for (var i = 0; i < 7; i++) {
                days.push(new Date(today.setDate(today.getDate() - today.getDay() + i)));

                var xpath = "//td[text()[contains(.,'" + names[i] + "')]]";
                var result = document.evaluate(xpath, document, null, XPathResult.ORDERED_NODE_SNAPSHOT_TYPE, null);

                for (var j = 0; j < result.snapshotLength; j++) {
                    var node = result.snapshotItem(j);
                    //console.log((days[i]).getMonth())
                    node.innerHTML = names[i] + " - " + month[(days[i]).getMonth()] + " " + days[i].getDate();
                }
            }

            try {
                document.getElementById('start').innerText = days[0].toLocaleDateString();
                document.getElementById('end').innerText = days[6].toLocaleDateString();
            } catch (error) {
                console.log(error);
            }
        }

        function nextWeek() {
            selectedDate.setDate(selectedDate.getDate() + 7);
            fillCalendarDays();
            updateDateExceptions();
        }

        function previousWeek() {
            selectedDate.setDate(selectedDate.getDate() - 7);
            fillCalendarDays();
            updateDateExceptions();
        }

        //fillCalendarDays();

        document.querySelector('#next').addEventListener('click', nextWeek)
        document.querySelector('#previous').addEventListener('click', previousWeek)

        ////////////////////////////////////////////////////////////
        // Save the old date values so we can rewrite them as needed
        function saveAndClearDefaultHours() {
            // Save default values and clear/empty the display
            tables.forEach(function(table) {
                var tBody = table.getElementsByTagName('tbody')[0];
                oldValues[table.id] = tBody.innerHTML;
                tBody.innerHTML = "";
            });
        }

        // Function called inside fillExceptions()...
        function fillRow(start, end, row, exceptions) {
            loc = row.cells[0].innerText;

            // Find any exceptions for this location
            exceptions.forEach(function(exception) {
                dateIndex = new Date(start);

                if (exception.location == 0 || locations[exception.location] == loc) {
                    // Loop through the row cells and set exceptions for any days as needed
                    exceptionStart = new Date(exception.started_at);
                    exceptionEnd = new Date(exception.ended_at);

                    for (var i = 1; i < row.cells.length; i++) {
                        // Check if the exception falls on this date
                        if (dateIndex >= exceptionStart &&
                            dateIndex <= exceptionEnd) {
                            row.cells[i].innerText = exception.description;
                        }

                        // Increment the date index
                        dateIndex.setDate(dateIndex.getDate() + 1);
                    }
                }
            })
        }

        // Function called inside updateDateExceptions()...
        function fillExceptions(start, end) {
            //console.log('End-Date = ', end);
            // fetch('https://library-apps.ucr.edu/api/hours-exceptions?from=' + start + '&to=' + end)
            fetch('/api/hours-exceptions?from=' + start + '&to=' + end)
                .then(res => res.json())
                .then(res => {
                    var tables = document.querySelectorAll('table');

                    // Set the values based on the selected date range
                    tables.forEach(function(table) {
                        // Reset the table data
                        document.getElementById(table.id).getElementsByTagName('tbody')[0].innerHTML =
                            oldValues[table.id];

                        var rows = table.rows;
                        for (let i = 1; i < rows.length; i++) {
                            fillRow(start, end, rows[i], res);
                        }
                    });
                })
        }

        function updateDateExceptions() {
            // NOTE: Start and End dates are set inside fillCalendarDays() function..
            var start = document.getElementById("start")?.innerText;
            var end = document.getElementById("end")?.innerText;

            fillExceptions(start, end);
        }

        function hideTables() {
            document.querySelector('table#rivera-hours').style.display = 'none'
            document.querySelector('table#orbach-hours').style.display = 'none'
            document.querySelector('table#scua-hours').style.display = 'none'
        }

        function showTables() {
            document.querySelector('table#rivera-hours').style.display = 'block'
            document.querySelector('table#rivera-hours').style.width = '100%'
            document.querySelector('table#orbach-hours').style.display = 'block'
            document.querySelector('table#orbach-hours').style.width = '100%'
            document.querySelector('table#scua-hours').style.display = 'block'
            document.querySelector('table#scua-hours').style.width = '100%'
        }
        // Event listener for readystatechange = interactive
        document.addEventListener('readystatechange', () => {
            // Save and Clear the default hours if ready state is interactive
            console.log('readystatechange event => ', document.readyState)
            if (document.readyState === 'interactive') {
                hideTables();
                console.log('completed hiding tables.')
                saveAndClearDefaultHours();
            }
            if (document.readyState === 'complete') {
                showTables();
                console.log('completed showing tables.')
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            fillCalendarDays();
            updateDateExceptions();
            showTables();
            console.log('Updated calendar content loaded successfully')
        })

        //NOTE (jv 03/27/2024) 'DOMSubtreeModified' has been deprecated - causing intermittent errors of displaying correct data.
        //document.querySelector('#end').addEventListener('DOMSubtreeModified', updateDateExceptions);
    </script>
=======
    // Function called inside updateDateExceptions()...
    function fillExceptions(start, end) {
        //console.log('End-Date = ', end);
        fetch('https://library-apps.ucr.edu/api/hours-exceptions?from=' + start + '&to=' + end)
            .then(res => res.json())
            .then(res => {
                var tables = document.querySelectorAll('table');

                // Set the values based on the selected date range
                tables.forEach(function(table) {
                    // Reset the table data
                    document.getElementById(table.id).getElementsByTagName('tbody')[0].innerHTML =
                        oldValues[table.id];

                    var rows = table.rows;
                    for (let i = 1; i < rows.length; i++) {
                        fillRow(start, end, rows[i], res);
                    }
                });
            })
    }

    function updateDateExceptions() {
        // NOTE: Start and End dates are set inside fillCalendarDays() function..
        var start = document.getElementById("start")?.innerText;
        var end = document.getElementById("end")?.innerText;

        fillExceptions(start, end);
    }

    function hideTables() {
        document.querySelector('table#rivera-hours').style.display = 'none'
        document.querySelector('table#orbach-hours').style.display = 'none'
        document.querySelector('table#scua-hours').style.display = 'none'
    }

    function showTables() {
        document.querySelector('table#rivera-hours').style.display = 'block'
        document.querySelector('table#rivera-hours').style.width = '100%'
        document.querySelector('table#orbach-hours').style.display = 'block'
        document.querySelector('table#orbach-hours').style.width = '100%'
        document.querySelector('table#scua-hours').style.display = 'block'
        document.querySelector('table#scua-hours').style.width = '100%'
    }
    // Event listener for readystatechange = interactive
    document.addEventListener('readystatechange', () => {
        // Save and Clear the default hours if ready state is interactive
        console.log('readystatechange event => ', document.readyState)
        if (document.readyState === 'interactive') {
            hideTables();
            console.log('completed hiding tables.')
            saveAndClearDefaultHours();
        }
        if (document.readyState === 'complete') {
            showTables();
            console.log('completed showing tables.')
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        fillCalendarDays();
        updateDateExceptions();
        showTables();
        console.log('Updated calendar content loaded successfully')
    })

    //NOTE (jv 03/27/2024) 'DOMSubtreeModified' has been deprecated - causing intermittent errors of displaying correct data.
    //document.querySelector('#end').addEventListener('DOMSubtreeModified', updateDateExceptions);
</script>
>>>>>>> main
</body>

</html>


