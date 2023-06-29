export function clickOnEnter(event) {
    if (event.key === "Enter") {
        event.target.dispatchEvent(new MouseEvent('click', { bubbles: true }));
    }
}