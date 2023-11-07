import './bootstrap';

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
