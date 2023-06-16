// @flow

import { apiFetch } from "../utils/network.js";
import type { ChatPostType } from "./chatSchema.js";

const chatRefreshInterval = 5000;

type Props = {|
    +chatApiLink: string,
    +initialPosts: $ReadOnlyArray<ChatPostType>,
    +initialNextPagePostId: ?number,
    +initialLatestPostId: number,
    +maxPostLength: number,
    +isModerator: boolean,
    +initialBanInfo: {|
        +name: string,
        +description: string,
        +timeRemaining: string,
    |};
    +memes: {|
        +codes: $ReadOnlyArray<string>,
        +images: $ReadOnlyArray<string>,
        +urls: $ReadOnlyArray<string>,
        +tests: $ReadOnlyArray<string>,
    |}
|};
function Chat({
    chatApiLink,
    initialPosts,
    initialNextPagePostId,
    initialLatestPostId,
    maxPostLength,
    isModerator,
    initialBanInfo,
    memes
}: Props) {
    const [banInfo, setBanInfo] = React.useState(initialBanInfo);
    const [posts, setPosts] = React.useState(initialPosts);

    const [nextPagePostId, setNextPagePostId] = React.useState(initialNextPagePostId);
    const [latestPostId, setLatestPostId] = React.useState(initialLatestPostId);
    // Only set if we're paginating
    const [previousPagePostId, setPreviousPagePostId] = React.useState(null);
    const currentPagePostIdRef = React.useRef(null);

    const [error, setError] = React.useState(null);

    if(banInfo.isBanned) {
        return <ChatBanInfo
            banName={banInfo.name}
            banDescription={banInfo.description}
            banTimeRemaining={banInfo.timeRemaining}
        />;
    }

    const refreshChat = function() {
        if(currentPagePostIdRef.current != null) {
            return;
        }

        apiFetch(
            chatApiLink,
            {
                request: 'load_posts',
            }
        ).then(handleApiResponse);
    };

    React.useEffect(() => {
        const intervalId = setInterval(refreshChat, chatRefreshInterval);

        return () => clearInterval(intervalId);
    }, []);

    function submitPost(message) {
        apiFetch(chatApiLink, {
            request: 'submit_post',
            message: message,
        }).then(handleApiResponse);
    }
    function deletePost(postId) {
        apiFetch(chatApiLink, {
            request: 'delete_post',
            post_id: postId,
        }).then(handleApiResponse);
    }

    function changePage(newStartingPostId: ?number) {
        if(newStartingPostId === currentPagePostIdRef.current) {
            return;
        }
        if(newStartingPostId == null || newStartingPostId >= latestPostId) {
            currentPagePostIdRef.current = null;
            apiFetch(chatApiLink, { request: 'load_posts' })
                .then(handleApiResponse);
            return;
        }
        if(newStartingPostId === currentPagePostIdRef.current) {
            return;
        }

        currentPagePostIdRef.current = newStartingPostId;
        apiFetch(
            chatApiLink,
            {
                request: 'load_posts',
                starting_post_id: newStartingPostId
            }
        ).then(handleApiResponse);
    }

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
        if(response.data.latestPostId != null) {
            setLatestPostId(response.data.latestPostId);
        }

        if(typeof response.data.previousPagePostId != "undefined") {
            setPreviousPagePostId(response.data.previousPagePostId);
        }
        if(typeof response.data.nextPagePostId != "undefined") {
            setNextPagePostId(response.data.nextPagePostId);
        }
    };

    return (
        <div>
            {error != null && <p className='systemMessage'>{error}</p>}
            <ChatInput
                maxPostLength={maxPostLength}
                memes={memes}
                submitPost={submitPost}
            />
            <ChatPosts
                posts={posts}
                previousPagePostId={previousPagePostId}
                nextPagePostId={nextPagePostId}
                isModerator={isModerator}
                deletePost={deletePost}
                goToNextPage={() => changePage(nextPagePostId)}
                goToPreviousPage={() => changePage(previousPagePostId)}
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

function ChatInput({maxPostLength, memes, submitPost}) {
    const [quickReply, _setQuickReply] = React.useState(
        JSON.parse(localStorage.getItem("quick_reply_on") ?? "true")
    );
    const [message, setMessage] = React.useState("");
    const [showMemeSelect, setShowMemeSelect] = React.useState(false);

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
    function handleMemeSelect(memeIndex: number) {
        setMessage(prevMessage => `${prevMessage}${memes.codes[memeIndex]}`);
        setShowMemeSelect(false);
    }

    const handlePostSubmit = React.useCallback(() => {
        submitPost(message);
        // TODO: Only clear this on a successful server response
        setMessage("");
    }, [message, submitPost]);

    const handleKeyDown = React.useCallback((e) => {
        if(e.code !== "Enter") {
            return;
        }

        if(quickReply && !e.shiftKey) {
            e.preventDefault();
            setMessage(e.target.value);
            handlePostSubmit();
        }
    }, [quickReply, handlePostSubmit]);

    const charactersRemaining = maxPostLength - message.length;
    const charactersRemainingDisplay = `Characters remaining: ${charactersRemaining} of ${maxPostLength}`;

    return (
        <div>
            {showMemeSelect &&
                <ChatMemeModal
                    memes={memes}
                    selectMeme={handleMemeSelect}
                    closeMemeSelect={() => setShowMemeSelect(false)}
                />
            }
            <table id="chat_input_table" className="table">
                <tbody>
                    <tr><th>Post Message</th></tr>
                    <tr><td style={{textAlign: "center"}}>
                        <button className="meme_toggle" onClick={() => setShowMemeSelect(!showMemeSelect)}>Memes</button><br />
                        <textarea
                            id="chat_input_box"
                            minLength="3"
                            maxLength={maxPostLength}
                            value={message}
                            onChange={e => setMessage(e.target.value)}
                            onKeyDown={handleKeyDown}
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

function ChatMemeModal({ memes, selectMeme, closeMemeSelect }) {
    return (
        <table id="meme_modal" className="table">
            <tbody>
                <tr>
                    <th>Memes</th>
                </tr>
                <tr>
                    <td>
                        <div id="meme_box">
                            {memes.codes.map((meme, i) => (
                                <div key={`meme:${i}`} className="meme_select">
                                    <img src={memes.urls[i]} onClick={() => selectMeme(i)} />
                                </div>
                            ))}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style={{ textAlign: "center" }}>
                        <button className="meme_toggle" onClick={closeMemeSelect}>Close</button>
                    </td>
                </tr>
            </tbody>
        </table>
    );
}

function ChatPosts({
    posts,
    previousPagePostId,
    nextPagePostId,
    isModerator,
    deletePost,
    goToPreviousPage,
    goToNextPage
}) {
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
                                        <a href={post.userProfileLink} className={post.userLinkClassNames.join(' ')}>
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
                                    <img
                                        className='delete_post_icon small_image'
                                        src='../images/delete_icon.png'
                                        onClick={() => deletePost(post.id)}
                                    />
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
                {previousPagePostId != null && <a className="paginationLink" onClick={goToPreviousPage}>Previous</a>}

                {nextPagePostId != null &&
                    <>
                        {previousPagePostId != null && <span>&nbsp;&nbsp;|&nbsp;&nbsp;</span>}
                        <a className="paginationLink" onClick={goToNextPage}>Next</a>
                    </>
                }
            </p>
        </div>
    );
}

window.Chat = Chat;