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

<table border="1" cellpadding="1" cellspacing="1" id="rivera-hours" style="width: 100%">
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
        <td>Closed</td>
        <td>7:30am - 8:00pm</td>
        <td>7:30am - 8:00pm</td>
        <td>7:30am - 8:00pm</td>
        <td>7:30am - 8:00pm</td>
        <td>7:30am - 8:00pm</td>
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
    </tbody>
</table>
<script type="text/javascript">
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
            }
        }

        try
        {
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
</script>
<script type="text/javascript" defer>
    // Save the old date values so we can rewrite them as needed
    var tables = document.querySelectorAll('table');
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

    tables.forEach(function (table) {
        var tBody = table.getElementsByTagName('tbody')[0];
        oldValues[table.id] = tBody.innerHTML;
    });

    function updateDateExceptions() {
        var start = document.getElementById("start")?.innerText;
        var end = document.getElementById("end")?.innerText;

        fillExceptions(start, end);
    }

    function fillExceptions(start, end) {
        console.log(end);
        fetch('https://library-apps.ucr.edu/api/hours-exceptions?from=' + start + '&to=' + end)
            .then(res => res.json())
            .then(res => {
                var tables = document.querySelectorAll('table');

                // Set the values based on the selected date range
                tables.forEach(function (table) {
                    // Reset the table data
                    document.getElementById(table.id).getElementsByTagName('tbody')[0].innerHTML = oldValues[table.id];
                    var rows = table.rows;

                    for (let i = 1; i < rows.length; i++) {
                        fillRow(start, end, rows[i], res);
                    }
                });
            })
    }

    function fillRow(start, end, row, exceptions) {
        loc = row.cells[0].innerText;

        // Find any exceptions for this location
        exceptions.forEach(function(exception) {
            dateIndex = new Date(start);

            if (exception.location == 0 || locations[exception.location] == loc ) {
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

    document.querySelector('#end').addEventListener('DOMSubtreeModified', updateDateExceptions);
</script>
</body>
</html>
