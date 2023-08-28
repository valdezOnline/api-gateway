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
    </body>
</html>
