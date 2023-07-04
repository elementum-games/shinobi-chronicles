// @flow

import { clickOnEnter } from "../utils/uiHelpers.js";

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
                        <select name="gender" defaultValue={registerPreFill.gender}>
                            <option value="Male">Male</option>
                            <option value="Female">Male</option>
                            <option value="Non-binary">Non-binary</option>
                            <option value="None">None</option>
                        </select>
                    </div>
                    <div className="register_village_wrapper">
                        <div className="register_field_label">village</div>
                        <select name="village" defaultValue={registerPreFill.village}>
                            <option value="Stone">Stone</option>
                            <option value="Cloud">Cloud</option>
                            <option value="Leaf">Leaf</option>
                            <option value="Sand">Sand</option>
                            <option value="Mist">Mist</option>
                        </select>
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

export function CreateCharacterButton({ onClick }) {
    return (
        <svg
            role="button"
            tabIndex="0"
            name="register"
            className="register_button"
            width="162"
            height="32"
            onClick={onClick}
            onKeyPress={clickOnEnter}
        >
            <radialGradient id="register_fill_default" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                <stop offset="0%" style={{ stopColor: '#84314e', stopOpacity: 1 }} />
                <stop offset="100%" style={{ stopColor: '#68293f', stopOpacity: 1 }} />
            </radialGradient>
            <radialGradient id="register_fill_click" cx="50%" cy="50%" r="50%" fx="50%" fy="50%">
                <stop offset="0%" style={{ stopColor: '#68293f', stopOpacity: 1 }} />
                <stop offset="100%" style={{ stopColor: '#84314e', stopOpacity: 1 }} />
            </radialGradient>
            <rect className="register_button_background" width="100%" height="100%" />
            <text className="register_button_shadow_text" x="81" y="18" textAnchor="middle" dominantBaseline="middle">create a character</text>
            <text className="register_button_text" x="81" y="16" textAnchor="middle" dominantBaseline="middle">create a character</text>
        </svg>
    );
}
