import { apiFetch } from "../utils/network.js";

// Initialize
function Topbar({ linkData }) {
    // Hooks - WIP notifications

    // API - WIP notifications

    // Utility

    // Content
    function displayTopbar() {
        return (
            <>
            <div className="topbar_left"></div>
            <div className={"topbar_right d-flex"}>
                <div className="topbar_inner_left"></div>
                <div className={"topbar_inner_center main_logo"}></div>
                <div className="topbar_inner_right">
                    <div className={"topbar_notifications_container d-flex"}>
                </div>
                </div>
            </div>
            </>
        )
    }

    // Misc
    function handleErrors(errors) {
        console.warn(errors);
        //setFeedback([errors, 'info']);
    }

    // Initialize
    React.useEffect(() => {

    }, []);

    // Display
    return (
        <div id="topbar" class="d-flex">
            {displayTopbar()}
        </div>
    )
}

window.Topbar = Topbar;