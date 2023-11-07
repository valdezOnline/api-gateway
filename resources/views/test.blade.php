<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Test</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
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
<script type="text/javascript" defer>
    // Save the old date values so we can rewrite them if needed
    var tables = document.querySelectorAll('table');
    var oldValues = {};

    tables.forEach(function (table) {
        var rows = table.rows;
        oldValues[table.id] = [];

        for (let i = 1; i < rows.length; i++) {
            oldValues[table.id].push(rows[i].cells[6].innerText);
        }
    });

    function updateDateExceptions() {
        var start = document.getElementById("start")?.innerText;
        var tables = document.querySelectorAll('table');

        // Set the values based on the selected date range
        tables.forEach(function (table) {
            var rows = table.rows;
            for (let i = 1; i < rows.length; i++) {
                switch(start) {
                    case "11/5/2023":
                        rows[i].cells[6].innerText = "Closed";
                        break;

                    case "11/19/2023":
                        /*
                        if (rows[i].cells[0].innerText == "Tomás Rivera Library") {
                            rows[i].cells[4].innerText = "7:30am - 5:00pm";
                        }
                        if (rows[i].cells[0].innerText == "Orbach Science Library") {
                            rows[i].cells[4].innerText = "7:30am - 5:00pm";
                        }
                        rows[i].cells[5].innerText = "Closed";
                        rows[i].cells[6].innerText = "Closed";
                         */
                        break;

                    default:
                        rows[i].cells[6].innerText = oldValues[table.id][i - 1];
                        break;
                }
            }
        });
    }

    document.querySelector('#start').addEventListener('DOMSubtreeModified', updateDateExceptions);

    updateDateExceptions();
</script>
</body>
</html>
