
let searchBar = document.getElementById('searchBar');
let tBody = document.getElementById('studentTableBody');

searchBar.addEventListener('input', function() {
    let searchValue = searchBar.value.toLowerCase();
    let studentRows = tBody.getElementsByTagName('tr');
    let regExp = new RegExp(searchValue, 'i');

    for (let row of studentRows) {
        let matchFound = false;
        let studentCols = row.getElementsByTagName('td');
        for(let col of studentCols){
            if(col.innerHTML.search(regExp) !== -1 && !col.classList.contains('studentTableActions')){
                matchFound = true;
                break;
            }
        }
        row.hidden = !matchFound;
    }
});

