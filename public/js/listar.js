// var GastosisHidden = true;
//     var VentasisHidden = true;

//     gastos = $(".rubrosGastos");
//     ingresos = $(".rubrosVentas");

//     function toogleGastosRubro() {
//       console.log("presiono Gastos");
//       if (GastosisHidden) {
//         gastos.show();
//         //toggleButton.textContent = "Ocultar filas";
//       }
//       else {
//         gastos.hide();
//         //toggleButton.textContent = "Mostrar filas";
//       }
//       GastosisHidden = !GastosisHidden;

//     }

//     function toogleVentasRubro() {
//       console.log("presiono Ventas");
//       if (VentasisHidden) ingresos.show();
//       else ingresos.hide();
//       VentasisHidden = !VentasisHidden;
//     }

//     // ajusta los headres con el body cuando la pantlla cambia de tamaño
//     $(window).on('resize', function () {
//           $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
//         });

//     $(dt.layout.table).on('resize', function(){
//       $($.fn.dataTable.tables(true)).DataTable().columns.adjust();


//     })

    function toggleElement (item) {
        let elements = document.getElementsByClassName(item);
        if(elements[0].style.display == "none") {
            for (const element of elements) {
                element.style.display = ""
            }
        }else {
            for (const element of elements) {
                element.style.display = "none"
            }
        }
    }
        // if(!element.style.display == undefined || element.style.display == "none") {
        //     element.style.display = "block"
        // }
        // if(element.style.display == "block"){
        //     element.style.display = "none"
        // }
        // const diaData = @json($r);
    // document.addEventListener("DOMContentLoaded", function () {
    //     const theadRow = document.getElementById("thead-row");
    //     const dias = window.diasData;
    //     console.log(dias);
    //     dias.forEach((dia) => {
    //       const fechaRaw = dia.egresos[0][1];

    //       const th = document.createElement("th");
    //       const a = document.createElement("a");
    //       a.className = "h7 text-center";

    //       if (fechaRaw !== "Totales" && fechaRaw !== "Incidencia (%)") {
    //         a.textContent = formatFecha(fechaRaw);
    //       } else {
    //         a.textContent = fechaRaw;
    //         th.classList.add("h5", "text-center");
    //       }

    //       th.appendChild(a);
    //       theadRow.appendChild(th);
    //     });
    //   });

    //   function formatFecha(fecha) {
    //     return dayjs(fecha).locale('es').format('dddd D-MMM');
    //   }

    //   document.addEventListener('DOMContentLoaded', function() {
    //     // Configuration
    //     // const indicators = [
    //     //     { id: 'temp', name: 'Temperature', description: 'System temperature' },
    //     //     { id: 'cpu', name: 'CPU Usage', description: 'Processing load' },
    //     //     { id: 'memory', name: 'Memory', description: 'RAM utilization' },
    //     //     { id: 'disk', name: 'Disk Space', description: 'Storage capacity' },
    //     //     { id: 'network', name: 'Network', description: 'Connection status' },
    //     //     { id: 'battery', name: 'Battery', description: 'Power level' },
    //     //     { id: 'updates', name: 'Updates', description: 'System updates' },
    //     //     { id: 'security', name: 'Security', description: 'Threat detection' },
    //     //     { id: 'backup', name: 'Backup', description: 'Data protection' },
    //     //     { id: 'errors', name: 'Errors', description: 'System errors' }
    //     // ];
    //     const indicators= window.diasData;

    //     // Track fixed columns - start with empty array
    //     let fixedColumns = [];

    //     // Get table container element
    //     const tableContainer = document.getElementById('tableContainer');

    //     // Track column positions and widths
    //     const columnData = {
    //         indicator: { position: 0, width: 0 },
    //         days: {}
    //     };

    //     // Track if fixed columns should actually be sticky
    //     let shouldApplySticky = true;

    //     // Initialize reset button functionality
    //     document.getElementById('resetColumns').addEventListener('click', function() {
    //         fixedColumns = []; // Clear all fixed columns
    //         updateFixedCount();
    //         generateTable();
    //     });

    //     // Initialize "Fix First Day" button
    //     document.getElementById('selectFirstColumn').addEventListener('click', function() {
    //         fixedColumns = [1]; // Fix only the first day
    //         updateFixedCount();
    //         generateTable();
    //     });

    //     // Initialize "Fix Multiple Days" button
    //     document.getElementById('fixMultipleColumns').addEventListener('click', function() {
    //         // Fix first 10 days
    //         fixedColumns = Array.from({length: 10}, (_, i) => i + 1);
    //         updateFixedCount();
    //         generateTable();
    //     });

    //     // Initialize "Fix Many Days" button
    //     document.getElementById('fixManyColumns').addEventListener('click', function() {
    //         // Fix first 20 days
    //         fixedColumns = Array.from({length: 20}, (_, i) => i + 1);
    //         updateFixedCount();
    //         generateTable();
    //     });

    //     // Update table when month changes
    //     document.getElementById('monthSelector').addEventListener('change', function() {
    //         generateTable(); // Regenerate table for new month
    //     });

    //     // Function to update fixed column count display
    //     function updateFixedCount() {
    //         document.getElementById('fixedCount').textContent = fixedColumns.length;
    //     }

    //     // Generate table initially
    //     generateTable();

    //     // Function to generate the table
    //     function generateTable() {
    //         const thead = document.querySelector('#indicatorTable thead');
    //         const tbody = document.querySelector('#indicatorTable tbody');

    //         // Clear existing content
    //         thead.innerHTML = '';
    //         tbody.innerHTML = '';

    //         const month = parseInt(document.getElementById('monthSelector').value);

    //         // Get days in month
    //         const daysInMonth = new Date(2025, month, 0).getDate();

    //         // Create header row
    //         const headerRow = document.createElement('tr');

    //         // Add indicator header (always first fixed column)
    //         const indicatorHeader = document.createElement('th');
    //         indicatorHeader.textContent = 'Indicator';
    //         indicatorHeader.className = 'fixed-column is-sticky';
    //         headerRow.appendChild(indicatorHeader);

    //         // Add day headers
    //         for (let day = 1; day <= daysInMonth; day++) {
    //             const th = document.createElement('th');
    //             th.textContent = `${day}`;
    //             th.dataset.day = day;

    //             // Mark fixed columns
    //             if (fixedColumns.includes(day)) {
    //                 th.className = 'fixed-user-column';
    //                 th.dataset.fixedIndex = fixedColumns.indexOf(day) + 1;
    //             }

    //             // Add click event to toggle fixed status
    //             th.addEventListener('click', function() {
    //                 toggleFixedColumn(day);
    //             });

    //             headerRow.appendChild(th);
    //         }

    //         thead.appendChild(headerRow);

    //         // Create indicator rows
    //         indicators.forEach(indicator => {
    //             const row = document.createElement('tr');

    //             // Add indicator name cell (first column)
    //             const indicatorCell = document.createElement('td');
    //             indicatorCell.className = 'fixed-column is-sticky';

    //             const cellHeader = document.createElement('div');
    //             cellHeader.className = 'cell-header';

    //             const title = document.createElement('div');
    //             title.className = 'indicator-title';
    //             title.textContent = indicator.name;

    //             const description = document.createElement('div');
    //             description.className = 'indicator-description';
    //             description.textContent = indicator.description;

    //             cellHeader.appendChild(title);
    //             cellHeader.appendChild(description);
    //             indicatorCell.appendChild(cellHeader);

    //             row.appendChild(indicatorCell);

    //             // Add status cells for all days
    //             for (let day = 1; day <= daysInMonth; day++) {
    //                 const td = document.createElement('td');
    //                 td.dataset.day = day;

    //                 // Mark fixed columns
    //                 if (fixedColumns.includes(day)) {
    //                     td.className = 'fixed-user-column';
    //                     td.dataset.fixedIndex = fixedColumns.indexOf(day) + 1;
    //                 }

    //                 const statusCell = document.createElement('div');
    //                 statusCell.className = 'status-cell';

    //                 // Generate deterministic random status
    //                 // Using a seeded value based on indicator and day
    //                 const seed = (indicator.id.charCodeAt(0) + day) % 100;
    //                 if (seed < 60) {
    //                     statusCell.classList.add('status-good');
    //                 } else if (seed < 80) {
    //                     statusCell.classList.add('status-warning');
    //                 } else if (seed < 95) {
    //                     statusCell.classList.add('status-critical');
    //                 } else {
    //                     statusCell.classList.add('status-neutral');
    //                 }

    //                 td.appendChild(statusCell);
    //                 row.appendChild(td);
    //             }

    //             tbody.appendChild(row);
    //         });

    //         // Update the fixed column count display
    //         updateFixedCount();

    //         // Calculate column positions and apply initial stickiness
    //         setTimeout(function() {
    //             calculateColumnData();
    //             applyFixedColumns();

    //             // Add scroll handler to dynamically check if fixed columns should be sticky
    //             tableContainer.removeEventListener('scroll', handleScroll);
    //             tableContainer.addEventListener('scroll', handleScroll);
    //         }, 0);

    //         // Add window resize handler to recalculate column data
    //         window.removeEventListener('resize', handleResize);
    //         window.addEventListener('resize', handleResize);
    //     }

    //     // Function to handle resize events
    //     function handleResize() {
    //         calculateColumnData();
    //         applyFixedColumns();
    //     }

    //     // Function to toggle fixed status of a column
    //     function toggleFixedColumn(day) {
    //         const index = fixedColumns.indexOf(day);

    //         if (index === -1) {
    //             // Add to fixed columns
    //             fixedColumns.push(day);
    //             // Sort the fixed columns in ascending order
    //             fixedColumns.sort((a, b) => a - b);
    //         } else {
    //             // Remove from fixed columns
    //             fixedColumns.splice(index, 1);
    //         }

    //         // Update the fixed column count display
    //         updateFixedCount();

    //         // Regenerate table to reflect changes
    //         generateTable();
    //     }

    //     // Function to calculate column positions and widths
    //     function calculateColumnData() {
    //         // Reset column data
    //         columnData.days = {};

    //         // Get the indicator column width
    //         const indicatorColumn = document.querySelector('.fixed-column');
    //         columnData.indicator.width = indicatorColumn.offsetWidth;
    //         columnData.indicator.position = 0;

    //         // Calculate positions for all fixed day columns
    //         let currentPosition = columnData.indicator.width;

    //         // Sort fixed columns to ensure proper positioning
    //         const sortedFixedColumns = [...fixedColumns].sort((a, b) => a - b);

    //         // Update all fixed columns' dataset.fixedIndex to match the sorted order
    //         sortedFixedColumns.forEach((day, index) => {
    //             const headerCells = document.querySelectorAll(`th[data-day="${day}"], td[data-day="${day}"]`);
    //             headerCells.forEach(cell => {
    //                 cell.dataset.fixedIndex = index + 1;
    //             });

    //             // Get the first header cell to measure width
    //             const headerCell = document.querySelector(`th[data-day="${day}"]`);
    //             if (headerCell) {
    //                 const columnWidth = headerCell.offsetWidth;

    //                 // Store position and width
    //                 columnData.days[day] = {
    //                     position: currentPosition,
    //                     width: columnWidth,
    //                     index: index + 1
    //                 };

    //                 // Update position for next column
    //                 currentPosition += columnWidth;
    //             }
    //         });
    //     }

    //     // Function to handle scroll events
    //     function handleScroll() {
    //         checkShouldApplySticky();
    //         applyFixedColumns();
    //     }

    //     // Function to check if fixed columns should be sticky based on container width
    //     function checkShouldApplySticky() {
    //         if (fixedColumns.length === 0) {
    //             shouldApplySticky = true;
    //             return;
    //         }

    //         const containerWidth = tableContainer.clientWidth;

    //         // Calculate total width of all fixed columns
    //         let totalFixedWidth = columnData.indicator.width; // Start with indicator column

    //         // Add width of each fixed day column
    //         Object.values(columnData.days).forEach(column => {
    //             totalFixedWidth += column.width;
    //         });

    //         // If total fixed width exceeds container width, don't apply sticky
    //         shouldApplySticky = totalFixedWidth <= containerWidth;
    //     }

    //     // Function to apply fixed columns based on current state
    //     function applyFixedColumns() {
    //         // Always treat indicator column specially
    //         const indicatorColumns = document.querySelectorAll('.fixed-column');
    //         indicatorColumns.forEach(col => {
    //             if (shouldApplySticky) {
    //                 col.classList.add('is-sticky');
    //                 col.style.left = '0';
    //             } else {
    //                 col.classList.remove('is-sticky');
    //                 col.style.left = '';
    //             }
    //         });

    //         // Get all fixed day columns
    //         const fixedDayColumns = document.querySelectorAll('.fixed-user-column');

    //         if (!shouldApplySticky) {
    //             // If we shouldn't apply sticky, remove all sticky styling
    //             fixedDayColumns.forEach(col => {
    //                 col.classList.remove('is-sticky');
    //                 col.style.left = '';
    //                 col.style.zIndex = '';
    //             });
    //             return;
    //         }

    //         // Sort days in ascending order
    //         const sortedDays = [...fixedColumns].sort((a, b) => a - b);

    //         // Apply sticky positioning to each fixed day column in order
    //         sortedDays.forEach(day => {
    //             const dayElements = document.querySelectorAll(`.fixed-user-column[data-day="${day}"]`);
    //             const columnInfo = columnData.days[day];

    //             if (!columnInfo) return;

    //             dayElements.forEach(element => {
    //                 element.classList.add('is-sticky');
    //                 element.style.left = `${columnInfo.position}px`;

    //                 // Set z-index: higher for headers, lower as we move right
    //                 const isHeader = element.tagName.toLowerCase() === 'th';
    //                 const zBase = isHeader ? 999 : 900;
    //                 element.style.zIndex = `${zBase - columnInfo.index}`;
    //             });
    //         });
    //     }
    // });









    document.addEventListener('DOMContentLoaded', function() {
        // Track fixed columns - start with empty array
        let fixedColumns = [];

        // Get table container element
        const tableContainer = document.getElementById('tableContainer');

        // Track column positions and widths
        const columnData = {
            indicator: { position: 0, width: 0 },
            days: {}
        };

        // Track if fixed columns should actually be sticky
        let shouldApplySticky = true;

        // Initialize reset button functionality
        document.getElementById('resetColumns').addEventListener('click', function() {
            fixedColumns = []; // Clear all fixed columns
            updateFixedCount();
            updateFixedColumns();
        });

        // Initialize "Fix First Day" button
        // document.getElementById('selectFirstColumn').addEventListener('click', function() {
        //     fixedColumns = [1]; // Fix only the first day
        //     updateFixedCount();
        //     updateFixedColumns();
        // });

        // Initialize "Fix Multiple Days" button
        // document.getElementById('fixMultipleColumns').addEventListener('click', function() {
        //     // Fix first 10 days
        //     const maxDays = Math.min(10, getTableDayCount());
        //     fixedColumns = Array.from({length: maxDays}, (_, i) => i + 1);
        //     updateFixedCount();
        //     updateFixedColumns();
        // });

        // Initialize "Fix Many Days" button
        // document.getElementById('fixManyColumns').addEventListener('click', function() {
        //     // Fix first 20 days
        //     const maxDays = Math.min(20, getTableDayCount());
        //     fixedColumns = Array.from({length: maxDays}, (_, i) => i + 1);
        //     updateFixedCount();
        //     updateFixedColumns();
        // });

        // Function to get total number of days in the table
        function getTableDayCount() {
            return document.querySelectorAll('#thead-row th[data-day]').length;
        }

        // Function to update fixed column count display
        function updateFixedCount() {
            document.getElementById('fixedCount').textContent = fixedColumns.length;
        }

        // Add click handling for day headers
        const dayHeaders = document.querySelectorAll('th[data-day]');
        dayHeaders.forEach(header => {
            header.addEventListener('click', function() {
                const day = parseInt(this.dataset.day);
                toggleFixedColumn(day);
            });
        });

        // Function to toggle fixed status of a column
        function toggleFixedColumn(day) {
            const index = fixedColumns.indexOf(day);

            if (index === -1) {
                // Add to fixed columns
                fixedColumns.push(day);
                // Sort the fixed columns in ascending order
                fixedColumns.sort((a, b) => a - b);
            } else {
                // Remove from fixed columns
                fixedColumns.splice(index, 1);
            }

            // Update the fixed column count display
            updateFixedCount();

            // Update fixed columns
            updateFixedColumns();
        }

        // Function to update the fixed columns
        function updateFixedColumns() {
            // Remove all fixed column classes first
            document.querySelectorAll('.fixed-user-column').forEach(el => {
                el.classList.remove('fixed-user-column', 'is-sticky');
                el.style.left = '';
                el.style.zIndex = '';
                delete el.dataset.fixedIndex;
            });

            // Apply fixed column classes to all selected columns
            fixedColumns.forEach((day, index) => {
                const dayCells = document.querySelectorAll(`[data-day="${day}"]`);
                dayCells.forEach(cell => {
                    cell.classList.add('fixed-user-column');
                    cell.dataset.fixedIndex = index + 1;
                });
            });

            // Calculate positions and apply stickiness
            calculateColumnData();
            checkShouldApplySticky();
            applyFixedColumns();
        }

        // Function to calculate column positions and widths
        function calculateColumnData() {
            // Reset column data
            columnData.days = {};

            // Get the indicator column width
            const indicatorColumn = document.querySelector('.fixed-column');
            columnData.indicator.width = indicatorColumn.offsetWidth;
            columnData.indicator.position = 0;

            // Calculate positions for all fixed day columns
            let currentPosition = columnData.indicator.width;

            // Sort fixed columns to ensure proper positioning
            const sortedFixedColumns = [...fixedColumns].sort((a, b) => a - b);

            // Calculate positions for all fixed day columns
            sortedFixedColumns.forEach((day, index) => {
                // Get the first header cell to measure width
                const headerCell = document.querySelector(`th[data-day="${day}"]`);
                if (headerCell) {
                    const columnWidth = headerCell.offsetWidth;

                    // Store position and width
                    columnData.days[day] = {
                        position: currentPosition,
                        width: columnWidth,
                        index: index + 1
                    };

                    // Update position for next column
                    currentPosition += columnWidth;
                }
            });
        }

        // Function to check if fixed columns should be sticky based on container width
        function checkShouldApplySticky() {
            if (fixedColumns.length === 0) {
                shouldApplySticky = true;
                return;
            }

            const containerWidth = tableContainer.clientWidth;

            // Calculate total width of all fixed columns
            let totalFixedWidth = columnData.indicator.width; // Start with indicator column

            // Add width of each fixed day column
            Object.values(columnData.days).forEach(column => {
                totalFixedWidth += column.width;
            });

            // If total fixed width exceeds container width, don't apply sticky
            shouldApplySticky = totalFixedWidth <= containerWidth;
        }

        // Function to apply fixed columns based on current state
        function applyFixedColumns() {
            // Always keep indicator column sticky
            const indicatorColumns = document.querySelectorAll('.fixed-column');
            indicatorColumns.forEach(col => {
                if (shouldApplySticky) {
                    col.classList.add('is-sticky');
                    col.style.left = '0';
                } else {
                    // col.classList.remove('is-sticky');
                    col.style.left = '';
                }
            });

            // Get all fixed day columns
            const fixedDayColumns = document.querySelectorAll('.fixed-user-column');

            if (!shouldApplySticky) {
                // If we shouldn't apply sticky, remove all sticky styling
                fixedDayColumns.forEach(col => {
                    // col.classList.remove('is-sticky');
                    col.style.left = '';
                    col.style.zIndex = '';
                });
                return;
            }

            // Sort days in ascending order
            const sortedDays = [...fixedColumns].sort((a, b) => a - b);

            // Apply sticky positioning to each fixed day column in order
            sortedDays.forEach(day => {
                const dayElements = document.querySelectorAll(`.fixed-user-column[data-day="${day}"]`);
                const columnInfo = columnData.days[day];

                if (!columnInfo) return;

                dayElements.forEach(element => {
                    element.classList.add('is-sticky');
                    element.style.left = `${columnInfo.position}px`;

                    // Set z-index: higher for headers, lower as we move right
                    const isHeader = element.tagName.toLowerCase() === 'th';
                    const zBase = isHeader ? 999 : 900;
                    element.style.zIndex = `${zBase - columnInfo.index}`;
                });
            });
        }

        // Add scroll handler to dynamically check if fixed columns should be sticky
        tableContainer.addEventListener('scroll', function() {
            checkShouldApplySticky();
            applyFixedColumns();
        });

        // Add window resize handler to recalculate column data
        window.addEventListener('resize', function() {
            calculateColumnData();
            applyFixedColumns();
        });

        // Initialize on page load
        calculateColumnData();
        applyFixedColumns();

        // Original toggle function (keep this from your original code)
        // window.toggleElement = function(className) {
        //     const elements = document.getElementsByClassName(className);
        //     for (let i = 0; i < elements.length; i++) {
        //         if (elements[i].style.display === 'none') {
        //             elements[i].style.display = '';
        //         } else {
        //             elements[i].style.display = 'none';
        //         }
        //     }
        // };
    });