import './bootstrap';

function fillCalendarDays($day) {
    const today = $day;
    const $days = [];
    const $names = ['Sun', 'Mon', 'Tues', 'Weds', 'Thurs', 'Fri', 'Sat'];

    // Get the days of the requested week
    for (var i = 0; i < 7; i++) {
        $days.push(new Date(today.setDate(today.getDate() - today.getDay() + i)));
        var xpath = "//td[text()='" + $names[i] + "']";
        var matchingElement = document.evaluate(xpath, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue;
        matchingElement.innerHTML = $names[i] + " " + $days[i].getDate();
    }
}

fillCalendarDays(new Date());
