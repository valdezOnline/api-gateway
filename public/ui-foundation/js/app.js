$(document).foundation();
$(document).foundation().Tabs;

// HTML Forms and Elements
const singleSearchInputWsKey = document.querySelector('#singleSearchWsKey')
const multiSearchInputWsKey = document.querySelector('#multiSearchWsKey')
const bulkSearchInputWsKey = document.querySelector('#bulkSearchWsKey')
const uploadFileNameInput = document.querySelector('#uploadFileName')

// Input Handler for Single Search WS key
singleSearchInputWsKeyhandler = function (e) {
    console.log(e.target.value)
    multiSearchInputWsKey.value = e.target.value
    bulkSearchInputWsKey.value = e.target.value;
}

// Input Handler for Multi Search WS key
multiSearchInputWsKeyhandler = function (e) {
    singleSearchInputWsKey.value = e.target.value;
    bulkSearchInputWsKey.value = e.target.value;
}

// Input Handler for Bulk Search WS key
bulkSearchInputWsKeyhandler = function (e) {
    singleSearchInputWsKey.value = e.target.value;
    multiSearchInputWsKey.value = e.target.value;
}


// Add the event listener for ALL WS key entry
singleSearchInputWsKey.addEventListener('input', singleSearchInputWsKeyhandler);
singleSearchInputWsKey.addEventListener('propertychange', singleSearchInputWsKeyhandler)
multiSearchInputWsKey.addEventListener('input', multiSearchInputWsKeyhandler);
multiSearchInputWsKey.addEventListener('propertychange', multiSearchInputWsKeyhandler)
bulkSearchInputWsKey.addEventListener('input', bulkSearchInputWsKeyhandler);
bulkSearchInputWsKey.addEventListener('propertychange', bulkSearchInputWsKeyhandler)


const singleSearchForm = document.querySelector('form#singleSearch')
const multiSearchForm = document.querySelector('form#multiSearch')
const bulkSearchForm = document.querySelector('form#bulkSearch')
const singleSearchInputOclcNumber = document.querySelector('input#singleSearchOclcNumber')
const multiSearchInputOclcNumbers = document.querySelector('textarea#multiSearchOclcNumbers')
const multiSearchMessage = document.querySelector('#multiSearchMessage')
const bulkSearchInputFile = document.querySelector('#csvFileUpload')
const bulkSearchEmail = document.querySelector('#bulkSearchEmail')

const modalErrorMessage = document.querySelector('#formErrorMessage')
const modalInfoMessage = document.querySelector('#formInfoMessage')

// Single Search Results
const dataTitle = document.querySelector('#data-title-singleSearch')
const dataAuthor = document.querySelector('#data-author-singleSearch')
const dataPublisher = document.querySelector('#data-publisher-singleSearch')
const dataDate = document.querySelector('#data-date-singleSearch')
const dataLibCount = document.querySelector('#data-lib-count-singleSearch')
const dataErrorSingleSearch = document.querySelector('#errorSingleSearchResult')
const dataErrorMultiSearch = document.querySelector('#errorMultiSearchResult')

// Parameters
let paramWsKey = ''
let paramOclc = ''

// Single Search Event Listener 
singleSearchForm.addEventListener('submit', (e) => {
    e.preventDefault()

    // Clear previous result
    clearSingleSearchResult()
    // See values entered
    console.log('singleSearch button clicked!')

    // Validate the form entries 
    if (singleSearchInputWsKey.value.toString().trim().length === 0) {
        alert('WS key is required.')
        this.showFormInputError("WS Key is required to do any search!")
    }
    else {
        // Save the value entered
        //console.log('WSkey = ', singleSearchInputWsKey.value.toString())
        let paramWsKey = singleSearchInputWsKey.value.toString().trim()

        if (singleSearchInputOclcNumber.value.toString().trim().length === 0) {
            //alert('OCLC Number is required.')
            this.showFormInputError("A single OCLC Number is required.")
        }
        else {
            if (!this.isNumeric(singleSearchInputOclcNumber.value.toString())) {
                console.log('Value of isNumeric = ', this.isNumeric(singleSearchInputOclcNumber.value.toString()))
                // return alert('OCLC Number must be numeric.')
                this.showFormInputError("A single OCLC Number must be numeric.")
            }
            else {
                // Save the value entered
                console.log('OCLC = ', singleSearchInputOclcNumber.value.toString())
                let paramOclc = singleSearchInputOclcNumber.value.toString().trim()

                // Clear ALL error messages
                this.clearErrorMessage()

                // Fetch data
                fetch('/api/v1/worldcat/singleSearch?OCLCnumber=' + paramOclc + '&wskey=' + paramWsKey)
                    .then((response) => {
                        console.log('From app.js --')
                        response.json().then((data) => {
                            console.log(data)
                            if (data.Error) {
                                // dataErrorSingleSearch.textContent = data.Error
                                this.showFormInputError(data.Error)
                                console.log('data.Error = ' + data.Error)
                            }
                            else {
                                console.log(data)
                                dataTitle.textContent = data.title
                                dataAuthor.textContent = data.author
                                dataPublisher.textContent = data.publisher
                                dataDate.textContent = data.date
                                dataLibCount.textContent = data.totalLibCount
                            }
                        })
                    }).catch((error) => {
                        // dataErrorSingleSearch.textContent = error
                        this.showFormInputError(error)
                        console.log('fetch error = ' + error)
                    })
            }
        }
    }
})

// Multi Search Event Listener
multiSearchForm.addEventListener('submit', (e) => {
    e.preventDefault()

    // Clear previous result
    clearMultiSearchResult()

    // Validate the form entries 
    if (multiSearchInputWsKey.value.toString().trim().length === 0) {
        // alert('WS key is required.')
        this.showFormInputError("WS Key is required to do any search!")
    }
    else {
        // Save the value entered
        //console.log('WSkey = ', multiSearchInputWsKey.value.toString())
        let paramWsKey = multiSearchInputWsKey.value.toString().trim()

        if (multiSearchInputOclcNumbers.value.toString().trim().length === 0) {
            // alert('OCLC Numbers is required.')
            this.showFormInputError("A list of OCLC Numbers (separated by a comma) is required.")
        }
        else {
            // Save the value entered
            console.log('OCLC(s) = ', multiSearchInputOclcNumbers.value.toString())
            let paramOclc = multiSearchInputOclcNumbers.value.toString().trim()

            // Clear ALL error messages
            this.clearErrorMessage()

            // Start the progress bar... ONLY if entries are more than 50
            if (paramOclc.split(",").length > 10) {
                this.showProgressBar()
            }

            //console.time("ElapsedTime")
            let timeStart = window.performance.now()
            // Fetch data
            fetch('/api/v1/worldcat/multiSearch?OCLCnumberList=' + paramOclc + '&wskey=' + paramWsKey)
                .then((response) => {
                    console.log('app.multiSearchForm --')
                    response.json().then((data) => {
                        console.log(data)
                        if (data.Error) {
                            // console.log('data.Error = ' + data.Error)
                            this.showFormInputError(data.Error)
                        }
                        else {
                            console.log(data)
                            this.clearMultiSearchResult()
                            // Get total record and populate table
                            let total = this.populateHtmlTable('multiSearchResult', data)
                            console.log('returned total = ', total)
                            multiSearchMessage.textContent = "Total Records Retrieved: " + total

                            this.closeProgressBar()
                            // Close the progress bar
                            if (multiSearchMessage.textContent.length !== 0) {
                                let timeEnd = window.performance.now()
                                this.closeProgressBar(total)
                                showElapsedTime(timeStart, timeEnd)
                            }
                        }
                    })
                }).catch((error) => {
                    this.showFormInputError(error)
                    console.log('fetch error = ' + error)
                })

        }
    }
})

// Bulk Upload File Event Listener
bulkSearchInputFile.addEventListener('input', (e) => {
    e.preventDefault()
    // Show file uploaded.
    console.log('Uploaded filename = ' + bulkSearchInputFile.files[0].name)
    uploadFileNameInput.value = bulkSearchInputFile.files[0].name
})


bulkSearchForm.addEventListener('submit', (e) => {
    e.preventDefault()

    console.log('bulkSearch button clicked!')

    // Validate the form entries 
    if (bulkSearchInputWsKey.value.toString().trim().length === 0) {
        //alert('WS key is required.')
        this.showFormInputError("WS Key is required to do any search!")
    }
    // Check upload file
    else {
        // Save the value entered
        let paramWsKey = bulkSearchInputWsKey.value.toString().trim()

        // Check Email entry
        if (bulkSearchEmail.value.toString().trim().length === 0) {
            //alert('Email is required.')
            this.showFormInputError("Email is required to send results to.")
        }
        else {
            // assign email
            let paramsEmail = bulkSearchEmail.value.toString().trim()
            if (bulkSearchInputFile.files.length === 0) {
                this.showFormInputError("A CSV file is required")
            }
            else {
                console.log('Input File = ' + bulkSearchInputFile.files.length)
                // Check if it's a CSV
                let fileExt = bulkSearchInputFile.files[0].name.substring(bulkSearchInputFile.files[0].name.length - 3, bulkSearchInputFile.files[0].name.length)
                if (fileExt.toLocaleUpperCase() !== 'CSV') {
                    this.showFormInputError("File must be a CSV")
                }
                else {
                    // Call the upload                     
                    const formData = new FormData()
                    formData.append('upload', bulkSearchInputFile.files[0])
                    const requestOptions = {
                        method: "POST",
                        body: formData,
                        redirect: "follow"
                    }
                    fetch('/api/v1/bulkSearch?wskey=' + paramWsKey + '&email=' + paramsEmail, requestOptions)
                        .then((response) => {
                            response.json().then((data) => {
                                // Check for Error:
                                if (data.Error) {
                                    this.showFormInputError(data.Error)
                                }
                                let fileUploaded = data
                                console.log('File Uploaded = ', fileUploaded)

                                let msg = "Your file has been successfully uploaded as (" + fileUploaded.file + ")."
                                msg += "The system will be sending you an email once it has completed processing your request."
                                this.clearInfoMessage()
                                this.showFormInfoMessage(msg)
                            })
                        })
                }
            }
        }
    }
})

var writeOutputFile = async (file, data, callback) => {
    try {
        const outFilename = "output_" + file;
        const outFilenamePath = downloadDir + "/" + outFilename
        const writableStream = fs.createWriteStream(outFilenamePath);
        const columns = [
            "oclc_number",
            "title",
            "author",
            "publisher",
            "date",
            "totalLibCount",
        ];

        const stringifier = stringify({ header: true, columns: columns, quoted_string: true });

        for (const row of data) {
            stringifier.write(row);
        }

        stringifier.pipe(writableStream);

        console.log("Finished writing data");
        callback('Writing completed.')
    } catch (error) {
        callback(error, undefined)
    }
}

var clearSingleSearchResult = function () {
    // dataErrorSingleSearch.textContent = ""
    dataTitle.textContent = ""
    dataAuthor.textContent = ""
    dataPublisher.textContent = ""
    dataDate.textContent = ""
    dataLibCount.textContent = ""
}

var clearMultiSearchResult = function () {
    // Clear the previous total
    document.querySelector('#multiSearchMessage').textContent = ""
    // Hide the export button
    $('#btnExportToExcel').addClass('hide')
    // Remove ALL previous result
    $('tr.rowData').remove()
    // Remove the data navigation
    $('nav#tableNav').remove()
}

var clearErrorMessage = function () {
    modalErrorMessage.textContent = ""
}

var clearInfoMessage = function () {
    modalInfoMessage.textContent = ""
}

var populateHtmlTable = function (tableId, data) {

    let tableRow = ''

    // Table Headers
    tableRow = "<tr>";
    for (var headers in data[0]) {
        tableRow += "<th>" + headers + "</th>";
    }
    tableRow += "</tr>";

    let rowCount = 0
    // Data Rows
    for (var eachItem in data) {
        rowCount++
        tableRow += "<tr class='rowData' style='align:left;'>";
        var dataObj = data[eachItem];
        for (var eachValue in dataObj) {
            tableRow += "<td>" + dataObj[eachValue] + "</td>";
        }
        tableRow += "</tr>";
    }

    console.log('Data Length: ', data.length)
    console.log('Table Row Count:', rowCount)

    document.getElementById(tableId).innerHTML = tableRow;

    // Before returning the count -paginate the table if result is greater than 15.
    if (rowCount > 15) {
        console.log('Paginating the table')
        paginateHtmlTable('#multiSearchResult', rowCount)
        //stylizedHtmlTable('#multiSearchResult');
    }

    // Before returning the count - un-hide the button to download to excel
    $('#btnExportToExcel').removeClass('hide')

    return rowCount
}

var stylizedHtmlTable = function (tableId) {
    //var DataTable = require('datatables.net');

    //let table = new DataTable(tableId, {
    $(tableId).DataTable({        // config options...
        // "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        dom: 'Blfrtip',
        // buttons: [
        //     {
        //         extend: 'excelHtml5',
        //         title: 'Excel File',
        //         text: 'Export to Excel'
        //     },
        //     {
        //         extend: 'csvHtml5',
        //         title: 'CSV File',
        //         text: 'Export to CSV'
        //     },
        //     {
        //         extend: 'pdfHtml5',
        //         title: 'PDF File',
        //         className: 'btn_pdf',
        //         text: 'Export to PDF'
        //     },
        // ]
    });
}
var paginateHtmlTable = function (tableId, rowCount) {
    // Add the navigation after the table
    $(tableId).after("<nav id='tableNav' aria-label='Pagination' class='text-center'><ul class='pagination' id='paginator'></ul></nav>")
    var rowsToShow = 10;
    var navPages = rowCount / rowsToShow;

    // Loop through the row data
    for (let i = 0; i < navPages; i++) {
        var pageNum = i + 1;
        $('ul#paginator').append('<li><a href="#panel2" aria-label="Page ' + i + '" style="text-decoration:underline;" rel="' + i + '"> ' + pageNum + '</a></li>')
    }

    // Hide other data
    $(tableId + ' tbody tr').hide();
    // Show ONLY the number of rows specified
    $(tableId + ' tbody tr').slice(0, rowsToShow).show();
    // Navigate and set the 1st page as active
    $('ul#paginator li a:first').addClass('current');

    // Bind the click function
    $('ul#paginator li a').bind('click', function () {
        // on click - remove active on previous page
        $('ul#paginator li a').removeClass('current');
        // set active the current page
        $(this).addClass('current');
        var currPage = $(this).attr('rel');
        var startItem = currPage * rowsToShow;
        var endItem = startItem + rowsToShow;
        $(tableId + ' tbody tr').css('opacity', '0.0').hide().slice(startItem, endItem)
            .css('display', 'table-row').animate({ opacity: 1 }, 300);
    })
}

var exportHtmlTableToExcel = function (tableId, fileName = '') {
    //const downloadLink = document.querySelector('[download="${fileName}"]')
    const dataType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    const tableSelect = document.getElementById(tableId);
    const tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

    // Specify file name
    fileName = fileName ? fileName + '.xls' : 'table-data.xls';

    // Create download link element
    const downloadLink = document.createElement("a");

    // if (downloadLink) {
    //     downloadLink.click()
    // } else {
    //     const downloadLink = document.createElement("a");
    // }

    document.body.appendChild(downloadLink);

    if (navigator.msSaveOrOpenBlob) {
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob(blob, fileName);
    } else {
        // Create a link to the file
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

        // Setting the file name
        downloadLink.download = fileName;

        //triggering the function
        downloadLink.click();
    }
}

var isNumeric = function (strEntry) {
    if (typeof strEntry != "string") { return false }

    return !isNaN(strEntry) && !isNaN(parseFloat(strEntry))
}

var showFormInputError = function (errMessage) {
    var modal = new Foundation.Reveal($('#formError'))
    modalErrorMessage.textContent = errMessage
    modal.open()
}

var showFormInfoMessage = function (infoMessage) {
    var modal = new Foundation.Reveal($('#formInfo'))
    modalInfoMessage.textContent = infoMessage
    modal.open()
}

var showProgressBar = function () {
    console.log('Opening progress bar.')
    var modal = new Foundation.Reveal($('#progressBar'))
    modal.open()

    // Before appending the progress bar 
    //- remove previous instance of the bar
    $('#progBar').remove()
    //- clear the total
    document.querySelector('#multiSearchResultTotal').textContent = ""
    //- clear the elapsed time
    document.querySelector('#multiSearchElapsedTime').textContent = ""
    //- hide the close button    
    $('#progressBarCloseButton').addClass('hide')

    let pbContainer = $('div.pb-container')

    // Set the progress bar
    let progressBar = $('<div id="progBar" class="progress-bar">Retrieving Data...</div>');

    // /* Append Progress Bar to Container and Queue Animation */
    pbContainer.append(progressBar).queue('Loading', function () {
        /* Animate Progress Bar for 50% */
        progressBar.animate({ width: '50%' }, 500, function () {
            /* Run Next Queue */
            //pbContainer.dequeue('Loading');
        });

        /* Animate Progress Bar for the remaining 85% */
        progressBar.animate({ width: '75%' }, 1000, function () {
            /* Run Next Queue */
            pbContainer.dequeue('Loading');
        });

        /* Animate Progress Bar for the remaining 85% */
        progressBar.animate({ width: '85%' }, 1500, function () {
            /* Run Next Queue */
            pbContainer.dequeue('Loading');
        });
    });

    /* Fall Back if Nothing is Animating */
    if (!progressBar.prevAll(':animated').length) {
        pbContainer.dequeue('Loading');
    }
}

var closeProgressBar = function (total) {
    // Set the progress bar to 100%
    $('#progBar').width('100%');
    // Find the button and remove the hide class
    $('#progressBarCloseButton').removeClass('hide')
    // Display the total records retrieved.
    document.querySelector('#multiSearchResultTotal').textContent = 'Total Records Retrieved: ' + total
}

var showElapsedTime = function (start, end) {
    let ms = (end - start)
    let min = Math.floor(ms / 60000);
    let sec = ((ms % 60000) / 1000).toFixed(0);
    document.querySelector('#multiSearchElapsedTime').textContent = 'Elapsed Time: ' + min + " minutes and " + (sec < 10 ? '0' : '') + sec + ' seconds.';
    return 'Elapsed Time: ' + min + " minutes and " + (sec < 10 ? '0' : '') + sec + ' seconds.';
}
