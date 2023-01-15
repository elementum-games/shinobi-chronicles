// formData.js

/**
 * Gets form data set from plain JS object
 *
 * Note: currently transforms nested objects into PHP-compatible format (pseudo-array names)
 *
 * @param data
 * @returns {FormData}
 */
export function buildFormData(data) {
    let formData = new FormData();

    formData = appendObjProps(formData, data);

    return formData;
}


function appendObjProps(formData, data, prefix = '') {
    Object.keys(data).forEach((key) => {
        let name;
        if(prefix) {
            name = prefix + '[' + key + ']';
        }
        else {
            name = key;
        }

        if(typeof data[key] === "object") {
            formData = appendObjProps(formData, data[key], name);
        }
        else {
            formData.append(name, data[key]);
        }
    });

    return formData;
}