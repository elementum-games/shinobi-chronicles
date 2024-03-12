export function numFormat(number, precision = 0) {
    return number.toLocaleString({
        minimumFractionDigits: precision,
        maximumFractionDigits: precision
    });
}