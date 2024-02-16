// @flow

import { apiFetch } from "../utils/network.js";
import { ModalProvider, useModal } from "../utils/modalContext.js"
import type { PlayerDataType } from "../_schema/userSchema";

type PremiumProps = {|
    +currentPage: string,
    +playerData: PlayerDataType
|};
function PremiumPage({
    page,
    playerData
 }: PremiumProps) {
    React.State = {
        currentPage: page
    }
    const characterChanges = "character_changes";
    const bloodlines = "bloodlines";
    const forbiddenSeal = "forbidden_seal";
    const purchaseAK = "purchase_ak";

    const [currentPage, setPage] = React.useState(page);

    function handlePageChange(newPage) {
        if(newPage !== currentPage) {
            setPage(newPage);
        }
    }


    return(
        <ModalProvider>
            <MarketHeader
                playerData={playerData}
            />
            <NavBar
                handlePageChange={handlePageChange}
                pages={[characterChanges, bloodlines, forbiddenSeal, purchaseAK]}
            />
            {currentPage === characterChanges &&
                <CharacterChanges />
            }
            {currentPage === bloodlines &&
                <Bloodlines />
            }
            {currentPage === forbiddenSeal &&
                <ForbiddenSeals />
            }
            {currentPage === purchaseAK &&
                <PurchaseAK />
            }
        </ModalProvider>
    );
}

function MarketHeader({
    playerData
}) {
    return(
        <div>
            Welcome to the ancient Market, where you can purchase premium features.<br />
            <b>Your Ancient Kunai:</b> {playerData.premiumCredits}
        </div>
    )
}
function NavBar({
    handlePageChange,
    pages
}) {
    return(
        <div className='navigation_row'>
            {pages.map(function(name) {
                return (
                    <div key={name} className='nav_button' onClick={() => handlePageChange(name)}>
                        {name.replace('_', ' ')}
                    </div>
                )
            })}
        </div>
    );
}

function CharacterChanges() {
    return(
        <div>
            Character Changes
        </div>
    );
}

function Bloodlines() {
    return(
        <div>
            Bloodlines
        </div>
    );
}

function ForbiddenSeals() {
    return(
        <div>
            Forbidden Seals
        </div>
    )
}

function PurchaseAK() {
    return(
        <>
            <div>
                Purchase AK
            </div>
            <AKMarket />
        </>
    );
}

function AKMarket() {
    return(
        <div>
            AK Market
        </div>
    );
}

window.PremiumPage = PremiumPage;