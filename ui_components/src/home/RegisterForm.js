// @flow

type RegisterFormProps = {|
    +registerErrorText: string,
    +registerPreFill: {
        +user_name: string,
        +village: "Stone" | "Cloud" | "Mist" | "Sand" | "Leaf",
        +email: string,
        +gender: "Male" | "Female" | "Non-binary" | "None",
    },
    +formRef: { current: ?HTMLFormElement },
|};
export function RegisterForm({ registerErrorText, registerPreFill, formRef }: RegisterFormProps): React$Node {
    return (
        <form id="register_form" action="" method="post" ref={formRef}>
            <div className="register_input_top">
                <input type="hidden" name="register" value="register" />
                <div className="register_username_container">
                    <div className="register_username_wrapper">
                        <label className="register_field_label">username</label>
                        <input type="text" name="user_name" className="register_username_input login_text_input" defaultValue={registerPreFill.user_name}/>
                    </div>
                </div>
                <div className="register_password_container">
                    <div className="register_password_wrapper">
                        <label className="register_field_label">password</label>
                        <input type="password" name="password" className="register_password_input login_text_input" />
                    </div>
                    <div className="register_confirm_wrapper">
                        <label className="register_field_label">confirm password</label>
                        <input type="password" name="confirm_password" className="register_password_confirm login_text_input" />
                    </div>
                </div>
                <div>
                    <div className="register_email_wrapper">
                        <label className="register_field_label">email</label>
                        <input type="text" name="email" className="login_username_input login_text_input" defaultValue={registerPreFill.email} />
                    </div>
                </div>
                <div>
                    <div className="register_email_notice">
                        (Note: Currently we cannot send emails to addresses from:<br />
                        hotmail.com, live.com, msn.com, outlook.com)
                    </div>
                </div>
                <div className="register_character_container">
                    <div className="register_gender_wrapper">
                        <div className="register_field_label">gender</div>
                        <div>
                            <input className="register_option" type="radio" id="register_gender_male" name="gender" value="Male" defaultChecked={registerPreFill.gender === "Male"} />
                            <label className="register_option_label" htmlFor="register_gender_male">Male</label>
                        </div>
                        <div>
                            <input className="register_option" type="radio" id="register_gender_female" name="gender" value="Female" defaultChecked={registerPreFill.gender === "Female"} />
                            <label className="register_option_label" htmlFor="register_gender_female">Female</label>
                        </div>
                        <div>
                            <input className="register_option" type="radio" id="register_gender_nonbinary" name="gender" value="Non-binary" defaultChecked={registerPreFill.gender === "Non-binary"} />
                            <label className="register_option_label" htmlFor="register_gender_nonbinary">Non-binary</label>
                        </div>
                        <div>
                            <input className="register_option" type="radio" id="register_gender_none" name="gender" value="None" defaultChecked={registerPreFill.gender === "None"} />
                            <label className="register_option_label" htmlFor="register_gender_none">None</label>
                        </div>
                    </div>
                    <div className="register_village_wrapper">
                        <div className="register_field_label">village</div>
                        <div>
                            <input className="register_option" type="radio" id="register_village_stone" name="village" value="Stone" defaultChecked={registerPreFill.village === "Stone"} />
                            <label className="register_option_label" htmlFor="register_village_stone">Stone</label>
                        </div>
                        <div>
                            <input className="register_option" type="radio" id="register_village_cloud" name="village" value="Cloud" defaultChecked={registerPreFill.village === "Cloud"} />
                            <label className="register_option_label" htmlFor="register_village_cloud">Cloud</label>
                        </div>
                        <div>
                            <input className="register_option" type="radio" id="register_village_leaf" name="village" value="Leaf" defaultChecked={registerPreFill.village === "Leaf"} />
                            <label className="register_option_label" htmlFor="register_village_leaf">Leaf</label>
                        </div>
                        <div>
                            <input className="register_option" type="radio" id="register_village_sand" name="village" value="Sand" defaultChecked={registerPreFill.village === "Sand"} />
                            <label className="register_option_label" htmlFor="register_village_sand">Sand</label>
                        </div>
                        <div>
                            <input className="register_option" type="radio" id="register_village_mist" name="village" value="Mist" defaultChecked={registerPreFill.village === "Mist"} />
                            <label className="register_option_label" htmlFor="register_village_mist">Mist</label>
                        </div>
                    </div>
                </div>
                <div>
                    <div className="register_terms_notice">
                        By clicking 'Create a Character' I affirm that I have read and agree to abide by the Rules and Terms of Service. I understand that if I fail to abide by the rules as determined by the moderating staff, I may be temporarily or permanently banned and that I will not be compensated for time lost. I also understand that any actions taken by anyone on my account are my responsibility.
                    </div>
                </div>
            </div>
            {registerErrorText !== "" &&
                <div className="register_input_bottom">
                    <div
                        className="login_error_label"
                        style={{
                            marginBottom: "30px",
                            marginLeft: "30px",
                            marginTop: "-15px"
                        }}>{registerErrorText}</div>
                </div>
            }
        </form>
    );
}
