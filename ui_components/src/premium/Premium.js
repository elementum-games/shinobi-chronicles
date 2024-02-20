// @flow

import { apiFetch } from "../utils/network.js";
import { ModalProvider } from "../utils/modalContext.js"
import { CharacterChanges } from "./CharacterChanges.js";
import type { PlayerDataType } from "../_schema/userSchema";

type PremiumProps = {|
    +userAPILink: string,
    +initialPage: string,
    +userAPIData: [],
    +genders: [],
    +skills: [],
|};
function PremiumPage({
    userAPILink,
    initialPage,
    userAPIData,
    genders,
    skills
 }: PremiumProps) {
    const characterChanges = "character_changes";
    const bloodlines = "bloodlines";
    const forbiddenSeal = "forbidden_seal";
    const purchaseAK = "purchase_ak";

    const [page, setPage] = React.useState(initialPage);
    const [playerData, setPlayerData] = React.useState(userAPIData.playerData);

    function handleAPIErrors(errors) {
        console.warn(errors);
    }

    function getPlayerData() {
        apiFetch(userAPILink, {
            request: 'getPlayerData'
        }).then(response => {
            if(response.errors.length) {
                handleAPIErrors(response.errors);
                return;
            }
            else {
                setPlayerData(response.data.playerData);
            }
        })
    }
    function handlePageChange(newPage) {
        if(newPage !== page) {
            setPage(newPage);
        }
    }

    // Initialize
    React.useEffect(() => {
        const playerDataInterval = setInterval(() => {
            getPlayerData();
        }, 15000);

        return () => clearInterval(playerDataInterval);
    }, []);

    // Display
    return(
        <ModalProvider>
            <MarketHeader
                playerData={playerData}
                pages={[characterChanges, bloodlines, forbiddenSeal, purchaseAK]}
                page={page}
                handlePageChange={handlePageChange}
            />
            {page === characterChanges &&
                <CharacterChanges
                    playerData={playerData}
                    genders={genders}
                    skills={skills}
                />
            }
            {page === bloodlines &&
                <Bloodlines />
            }
            {page === forbiddenSeal &&
                <ForbiddenSeals />
            }
            {page === purchaseAK &&
                <PurchaseAK />
            }
        </ModalProvider>
    );
}

type headerProps = {|
    +playerData: PlayerDataType,
    +pages: $ReadOnlyArray,
    +handlePageChange: function
|}
function MarketHeader({
    playerData,
    pages,
    page,
    handlePageChange
}: headerProps) {
    return(
        <>
            <div className="box-primary">
                <NavBar
                    handlePageChange={handlePageChange}
                    pages={pages}
                    page={page}
                />
                <p className="center">
                    Welcome to the Ancient Market! The vendors you find here seek something <b>more valuable</b> than just yen.<br />
                    You can trade your <em>Ancient Kunai</em> to purchase and manage premium benefits.<br />
                    <br />
                    Ancient Kunai: {playerData.premiumCredits.toLocaleString('US')}
                    <br />
                    &yen;{playerData.money.toLocaleString('US')}
                </p>
            </div>
            <br />
        </>
    )
}
function NavBar({
    handlePageChange,
    pages,
    page
}) {
    return(
        <div className='navigation_row'>
            {pages.map(function(name) {
                let buttonClass = (name === page) ? 'nav_button selected' : 'nav_button';

                return (
                    <div key={name} className={buttonClass} onClick={() => handlePageChange(name)}>
                        {name.replace('_', ' ')}
                    </div>
                )
            })}
        </div>
    );
}

function Bloodlines() {
    return(
        <table className="table">
            <tbody>
            <tr><th>Bloodline</th></tr>
            </tbody>
        </table>
    );
}

function ForbiddenSeals() {
    return(
        <table className="table">
            <tbody>
            <tr><th>Forbidden Seal</th></tr>
            </tbody>
        </table>
    );
}

function PurchaseAK() {
    return(
        <>
            <table className="table">
                <tbody>
                <tr><th>Purchase Ancient Kunai</th></tr>
                </tbody>
            </table>
            <AKMarket />
        </>
    );
}

function AKMarket() {
    return(
        <table className="table">
            <tbody>
            <tr><th>Anicnet Kunai Market</th></tr>
            </tbody>
        </table>
    );
}

window.PremiumPage = PremiumPage;