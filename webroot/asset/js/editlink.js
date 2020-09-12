/**
 * remove a tag from the tag "cloud"
 * @param String tagString
 * @param String targetStartString
 */
function removeTag(tagString,targetStartString) {
    let toRemove = document.getElementById(targetStartString + '-' + tagString);
    let saveInput = document.getElementById(targetStartString + '-save');

    if(toRemove && saveInput) {
        let newSaveValue = _removeFromCommaString(saveInput.value,tagString);
        saveInput.value = newSaveValue;
        toRemove.remove();
    }
    else {
        console.log("Delete element not found");
    }
}

/**
 * add a tag to the visible tag "cloud" and hidden form input
 * used in the form for saving
 * @param Object e
 * @param String targetStartString
 */
function addTag(e,targetStartString) {
    e = e || window.event;

    if(e.keyCode === 13) {
        let elem = e.srcElement || e.target;
        let saveInput = document.getElementById(targetStartString + '-save');
        let listBox = document.getElementById(targetStartString + '-listbox');
        let newTagTemplate = document.getElementById(targetStartString + '-template');

        let checkString = _checkForSpaceString(elem.value,'nospace');

        if(saveInput && listBox && elem && newTagTemplate && checkString) {
            let toAdd = elem.value;
            let newSaveValue = _appendToCommaString(saveInput.value,toAdd);

            let newT = newTagTemplate.cloneNode(true);
            newT = _fillTagTemplate(newT,toAdd,targetStartString);
            listBox.appendChild(newT);

            saveInput.value = newSaveValue;
        }

        elem.value = '';
        e.preventDefault();
    }
}

/**
 * add given string to given existing string seperated with a comma
 * @param String theString
 * @param String toAdd
 * @returns {string}
 * @private
 */
function _appendToCommaString(theString,toAdd) {
    if(theString.length > 0 && toAdd.length > 0) {
        let theArray = theString.split(',');
        if(!theArray.includes(toAdd)) {
            theString = theString + "," + toAdd
        }
    }
    else if (toAdd.length > 0) {
        theString = toAdd;
    }

    return theString;
}

/**
 * add given string to given existing string seperated with a comma
 * @param String theString
 * @param String toAdd
 * @returns {string}
 * @private
 */
function _removeFromCommaString(theString,toRemove) {
    if(theString.length > 0 && toRemove.length > 0) {
        let theArray = theString.split(',');

        if(theArray.includes(toRemove)) {
            for( let i = theArray.length-1; i >= 0; i--){
                if ( theArray[i] === toRemove) theArray.splice(i, 1);
            }

            theString = theArray.join(",");
        }
    }

    return theString;
}

/**
 * remove from given list the given value if it exists
 * @param Object list
 * @param String value
 * @private
 */
function _removeFromDatalist(list, value) {
    if(list.options.length > 0 && value && value.length > 0) {
        for (i = 0; i < list.options.length; i++) {
            if(list.options[i].value == value) {
                list.options[i].remove();
            }
        }
    }
}

/**
 * fill the tag template with the right data and js calls
 * depends on the html template created in the html code
 * @param Object el The already cloned node
 * @param String newTagString The new tag name
 * @param String targetStartString
 * @returns Object the cloned el
 * @private
 */
function _fillTagTemplate(el,newTagString,targetStartString) {
    el.removeAttribute('style');
    el.setAttribute('id',targetStartString + '-' + newTagString);

    let spanEl = el.querySelector('span');
    spanEl.innerHTML = newTagString;

    let aEl = el.querySelector('a');
    aEl.setAttribute('onclick', "removeTag('"+newTagString+"','"+targetStartString+"');");

    return el;
}

/**
 * simple check if the string is empty or contains whitespace chars
 * @param stringTocheck
 * @returns {boolean}
 * @private
 */
function _checkForSpaceString(stringTocheck) {
    let check = stringTocheck.replace(/\s/gm,'');
    if(check === stringTocheck && check.length > 0) {
        return true;
    }
    return false;
}
