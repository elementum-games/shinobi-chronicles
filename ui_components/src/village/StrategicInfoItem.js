export function StrategicInfoItem({ strategicInfoData, getPolicyDisplayData }) {
    function getStrategicInfoBanner(village_id) {
        switch (village_id) {
            case 1:
                return '/images/v2/decorations/strategic_banners/stratbannerstone.jpg';
            case 2:
                return '/images/v2/decorations/strategic_banners/stratbannercloud.jpg';
            case 3:
                return '/images/v2/decorations/strategic_banners/stratbannerleaf.jpg';
            case 4:
                return '/images/v2/decorations/strategic_banners/stratbannersand.jpg';
            case 5:
                return '/images/v2/decorations/strategic_banners/stratbannermist.jpg';
            default:
                return null;
        }
    }
    return (
        <div className="strategic_info_item">
            <div className="strategic_info_name_wrapper">
                <div className="strategic_info_name">{strategicInfoData.village.name}</div>
                <div className="strategic_info_policy">{getPolicyDisplayData(strategicInfoData.village.policy_id).name}</div>
            </div>
            <div className="strategic_info_banner" style={{ backgroundImage: "url(" + getStrategicInfoBanner(strategicInfoData.village.village_id) + ")" }}></div>
            <div className="strategic_info_top">
                <div className="column">
                    <div className="strategic_info_kage_wrapper">
                        <div className="strategic_info_label">kage:</div>
                        {strategicInfoData.seats.find(seat => seat.seat_type == "kage").user_name ?
                            <div className="strategic_info_seat"><a href={"/?id=6&user=" + strategicInfoData.seats.find(seat => seat.seat_type == "kage").user_name}>{strategicInfoData.seats.find(seat => seat.seat_type == "kage").user_name}</a></div> :
                            <div className="strategic_info_seat">-None-</div>
                        }
                    </div>
                    <div className="strategic_info_elder_wrapper">
                        <div className="strategic_info_label">elders:</div>
                        <div className="strategic_info_elders">
                            {strategicInfoData.seats.filter(seat => seat.seat_type == "elder")
                                .map((elder, index) => (
                                    elder.user_name ?
                                        <div key={elder.seat_key} className="strategic_info_seat"><a href={"/?id=6&user=" + elder.user_name}>{elder.user_name}</a></div> :
                                        <div key={elder.seat_key} className="strategic_info_seat">-None-</div>
                                ))}
                        </div>
                    </div>
                    <div className="strategic_info_points_wrapper">
                        <div className="strategic_info_label">points:</div>
                        <div className="strategic_info_points">{strategicInfoData.village.points}</div>
                    </div>
                    <div className="strategic_info_enemy_wrapper">
                        <div className="strategic_info_label">at war with <img className="strategic_info_war_icon" src="/images/icons/war.png" /></div>
                        <div className="strategic_info_relations">
                            {strategicInfoData.enemies
                                .map((enemy, index) => (
                                    <div key={index} className="strategic_info_relation_item">{enemy}</div>
                                ))}
                        </div>
                    </div>
                </div>
                <div className="column">
                    <div className="strategic_info_population_wrapper">
                        <div className="strategic_info_label">village ninja:</div>
                        <div className="strategic_info_population">
                            {strategicInfoData.population
                                .map((rank, index) => (
                                    <div key={rank.rank} className="strategic_info_population_item">{rank.count + " " + rank.rank}</div>
                                ))}
                            <div className="strategic_info_population_item total">{strategicInfoData.population.reduce((acc, rank) => acc + rank.count, 0)} total</div>
                        </div>
                    </div>
                    <div className="strategic_info_ally_wrapper">
                        <div className="strategic_info_label">allied with <img className="strategic_info_war_icon" src="/images/icons/ally.png" /></div>
                        <div className="strategic_info_relations">
                            {strategicInfoData.allies
                                .map((ally, index) => (
                                    <div key={index} className="strategic_info_relation_item">{ally}</div>
                                ))}
                        </div>
                    </div>
                </div>
            </div>
            <div className="strategic_info_bottom">
                <div className="column">
                    <div className="strategic_info_region_wrapper">
                        <div className="strategic_info_label">regions owned:</div>
                        <div className="strategic_info_regions">
                            {strategicInfoData.regions
                                .map((region, index) => (
                                    <div key={region.name} className="strategic_info_region_item">{region.name}</div>
                                ))}
                        </div>
                    </div>
                </div>
                <div className="column">
                    <div className="strategic_info_resource_wrapper">
                        <div className="strategic_info_label">resource points:</div>
                        <div className="strategic_info_supply_points">
                            {Object.values(strategicInfoData.supply_points)
                                .map((supply_point, index) => (
                                    <div key={index} className="strategic_info_supply_item"><span className="supply_point_name">{supply_point.name}</span> x{supply_point.count}</div>
                                ))}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}