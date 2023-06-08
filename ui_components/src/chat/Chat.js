// @flow

import { apiFetch } from "../utils/network.js";

const chatRefreshInterval = 5000;

function Chat({
    chatApiLink,
    initialPosts,
    initialNextPageIndex,
    initialMaxPostIndex,
    maxPostLength,
    isModerator,
    initialBanInfo,
}) {
    const [banInfo, setBanInfo] = React.useState(initialBanInfo);
    const [posts, setPosts] = React.useState(initialPosts);
    const [previousPageIndex, setPreviousPageIndex] = React.useState(0);
    const [currentPageIndex, setCurrentPageIndex] = React.useState(0);
    const [nextPageIndex, setNextPageIndex] = React.useState(initialNextPageIndex);
    const [maxPostIndex, setMaxPostIndex] = React.useState(initialMaxPostIndex);

    const [error, setError] = React.useState(null);

    if(banInfo.isBanned) {
        return <ChatBanInfo
            banName={banInfo.name}
            banDescription={banInfo.description}
            banTimeRemaining={banInfo.timeRemaining}
        />;
    }

    function getPosts() {
        apiFetch('/api/chat.php', {
            request: 'load_posts',
        }).then(handleApiResponse);
    }
    function submitPost(message) {
        apiFetch('/api/chat.php', {
            request: 'submit_post',
            message: message,
        }).then(handleApiResponse);
    }
    function deletePost(postId) {
        apiFetch('/api/chat.php', {
            request: 'delete_post',
            post_id: postId,
        }).then(handleApiResponse);
    }

    React.useEffect(() => {
        const intervalId = setInterval(getPosts, chatRefreshInterval);

        return () => clearInterval(intervalId);
    }, []);

    const handleApiResponse = (response) => {
        if(response.data.banInfo && response.data.banInfo.isBanned) {
            setBanInfo(response.data.banInfo);
            return;
        }

        if(response.errors.length > 0) {
            setError(response.errors.join(' '));
        }
        else {
            setError(null);
        }

        if(response.data.posts != null) {
            setPosts(response.data.posts);
        }
        if(response.data.previousPageIndex != null) {
            setPreviousPageIndex(response.data.previousPageIndex);
        }
        if(response.data.currentPageIndex != null) {
            setCurrentPageIndex(response.data.currentPageIndex);
        }
        if(response.data.nextPageIndex != null) {
            setNextPageIndex(response.data.nextPageIndex);
        }
        if(response.data.maxPostIndex != null) {
            setMaxPostIndex(response.data.maxPostIndex);
        }
    };

    return (
        <div>
            {error != null && <p className='systemMessage'>{error}</p>}
            <ChatInput
                maxPostLength={maxPostLength}
                submitPost={submitPost}
            />
            <ChatPosts
                posts={posts}
                previousPostIndex={previousPageIndex}
                currentPageIndex={currentPageIndex}
                nextPageIndex={nextPageIndex}
                maxPostIndex={maxPostIndex}
                isModerator={isModerator}
                deletePost={deletePost}
            />
        </div>
    )
}

function ChatBanInfo({ banName, banDescription, banTimeRemaining }) {
    return (
        <table className="table">
            <tbody>
                <tr>
                    <th>{banName}</th>
                </tr>
                <tr>
                    <td style={{textAlign: "center"}}>
                        {banDescription}
                        {banTimeRemaining != null && <span><b>Time Remaining:</b> {banTimeRemaining}</span>}
                        <br/>
                        Visit the <a href="/support.php">Support Center</a> to appeal this.
                    </td>
                </tr>
            </tbody>
        </table>
    );
}

function ChatInput({maxPostLength, submitPost}) {
    const [quickReply, _setQuickReply] = React.useState(
        JSON.parse(localStorage.getItem("quick_reply_on") ?? "true")
    );
    const [message, setMessage] = React.useState("");

    /*$(document).on("click", ".meme_select", function () {
        // Chat.val(Chat.val() + $(this).attr("data-code"));
        $("#meme_modal").addClass("hidden");
        $("#meme_toggle").text("+ Meme");
    });
    $(document).on("click", ".meme_toggle", function (e) {
        $("#meme_modal").toggleClass("hidden");
    });*/

    function setQuickReply(newValue: boolean) {
        localStorage.setItem("quick_reply_on", JSON.stringify(newValue));
        _setQuickReply(newValue);
    }

    const handlePostSubmit = React.useCallback(() => {
        submitPost(message);
        // TODO: Only clear this on a successful server response
        setMessage("");
    }, [message, submitPost]);

    const handleKeyDown = React.useCallback((e: KeyboardEvent) => {
        if(e.which !== 13) {
            return;
        }

        if(quickReply && !e.shiftKey) {
            e.preventDefault();
            handlePostSubmit();
        }
    }, [quickReply, message, submitPost]);

    function handleInputFocus() {
        document.addEventListener('keydown', handleKeyDown);
    }
    function handleInputBlur() {
        document.removeEventListener('keydown', handleKeyDown);
    }

    const charactersRemaining = maxPostLength - message.length;
    const charactersRemainingDisplay = `Characters remaining: ${charactersRemaining} of ${maxPostLength}`;

    return (
        <div>
            <table id="chat_input_table" className="table">
                <tbody>
                    <tr><th>Post Message</th></tr>
                    <tr><td style={{textAlign: "center"}}>
                        <button className="meme_toggle">Meme</button><br />
                        <textarea
                            id="chat_input_box"
                            minLength="3"
                            maxLength={maxPostLength}
                            value={message}
                            onChange={e => setMessage(e.target.value)}
                            onFocus={handleInputFocus}
                            onBlur={handleInputBlur}
                        ></textarea><br/>
                        <input type="checkbox" checked={quickReply} onChange={e => setQuickReply(e.target.checked)} /> Quick reply<br/>
                        <span className="red">{charactersRemaining < 50 && charactersRemainingDisplay}</span>
                        <br/>
                        <button onClick={handlePostSubmit}>Post</button>
                    </td></tr>
                </tbody>
            </table>
        </div>
    )
}

function ChatMemeModal({ memes }) {
    const memeCodes = memes.map(meme => meme.code);

    return (
        <table id="meme_modal" className="table hidden">
            <tbody>
                <tr>
                    <th>Memes</th>
                </tr>
                <tr>
                    <td>
                        <div id="meme_box">
                            {memes.map((meme, i) => (
                                <div key={`meme:${i}`} data-code={meme.code} className="meme_select">
                                    {meme.image}
                                </div>
                            ))}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style={{ textAlign: "center" }}>
                        <button className="meme_toggle">Close</button>
                    </td>
                </tr>
            </tbody>
        </table>
    );
}

function ChatPosts({ posts, currentPageIndex, maxPostIndex, isModerator, deletePost }) {
    function handlePreviousClick() {

    }
    function handleNextClick() {

    }

    return (
        <div>
            <table className="table" style={{width: "98%"}}>
                <tbody>
                    <tr>
                        <th style={{ width: "28%" }}>Users</th>
                        <th style={{ width: "61%" }}>Message</th>
                        <th style={{ width: "10%" }}>Time</th>
                    </tr>

                    {posts.length < 1 &&
                        <tr>
                            <td colSpan="3" style={{ textAlign: "center" }}>No posts!</td>
                        </tr>
                    }

                    {posts.map((post, i) => (
                        <tr key={`post:${i}`} className="chat_msg" style={{textAlign: "center"}}>
                            <td>
                                <div id='user_data_container'>
                                    <div className="avatarContainer">
                                        <img src={post.avatarLink} />
                                    </div>
                                    <div className="character_info">
                                        <a href={post.userProfileLink} className={`${post.class} ${post.statusType}`}>
                                            {post.userName}
                                        </a><br/>
                                        <p>
                                            <img
                                                className='villageIcon'
                                                src={`./images/village_icons/${post.userVillage.toLowerCase()}.png`}
                                                alt={`${post.userVillage} Village`}
                                                title={`${post.userVillage} Village`}
                                            />
                                            {post.userTitle}
                                        </p>
                                    </div>
                                </div>

                                {post.staffBannerName &&
                                    <p className="staffMember" style={{ backgroundColor: post.staffBannerColor }}>
                                        {post.staffBannerName}
                                    </p>
                                }
                            </td>
                            <td dangerouslySetInnerHTML={{__html: post.message}}></td>
                            <td style={{ fontStyle: "italic" }}>
                                <div style={{ marginBottom: "2px"}}>{post.timeString}</div>
                                {isModerator &&
                                    <a className='imageLink' onClick={() => deletePost(post.id)}>
                                        <img className='small_image' src='../images/delete_icon.png'/>
                                    </a>
                                }
                                <a className='imageLink' href={post.reportLink}>
                                    <img className='small_image' src='../images/report_icon.png'/>
                                </a>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>

            <p style={{textAlign: "center"}}>
                {currentPageIndex > 0 && <a onClick={handlePreviousClick}>Previous</a>}

                {currentPageIndex < maxPostIndex &&
                    <>
                        {currentPageIndex !== 0 && "&nbsp;&nbsp;|&nbsp;&nbsp;"}
                        <a onClick={handleNextClick}>Next</a>
                    </>
                }
            </p>
        </div>
    );
}

window.Chat = Chat;