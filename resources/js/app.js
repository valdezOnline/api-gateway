import './bootstrap';

function printStartAndEndOfCurrentWeek() {
    const today = new Date();

    // Get the first day of the current week (Sunday)
    const firstDay = new Date(
        today.setDate(today.getDate() - today.getDay()),
    );

    // Get the last day of the current week (Saturday)
    const lastDay = new Date(
        today.setDate(today.getDate() - today.getDay() + 6),
    );

    console.log(firstDay);
    console.log(lastDay);
}

printStartAndEndOfCurrentWeek();
