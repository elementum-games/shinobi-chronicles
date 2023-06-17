// @flow

import { apiFetch } from "../utils/network.js";
import type { ChatPostType } from "./chatSchema.js";

const chatRefreshInterval = 5000;

type Props = {|
    +chatApiLink: string,
    +initialPosts: $ReadOnlyArray < ChatPostType >,
    +initialPostId: ?number,
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
    initialPostId,
    initialNextPagePostId,
    initialPreviousPagePostId,
    initialLatestPostId,
    maxPostLength,
    isModerator,
    initialBanInfo,
    memes,
}: Props) {
    const [banInfo, setBanInfo] = React.useState(initialBanInfo);
    const [posts, setPosts] = React.useState(initialPosts);

    const [nextPagePostId, setNextPagePostId] = React.useState(initialNextPagePostId);
    const [latestPostId, setLatestPostId] = React.useState(initialLatestPostId);
    // Only set if we're paginating
    const [previousPagePostId, setPreviousPagePostId] = React.useState(initialPreviousPagePostId);
    const initialPostIdRef = React.useRef(initialPostId);
    const currentPagePostIdRef = React.useRef(null);

    const [error, setError] = React.useState(null);

    const [message, setMessage] = React.useState("");

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

        if (initialPostIdRef.current != null) {
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
        initialPostIdRef.current = null;
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

    function quotePost(postId) {
        setMessage(prevMessage => `${prevMessage}[quote:${postId}]`);
    }

    return (
        <div>
            {error != null && <p className='systemMessage'>{error}</p>}
            <ChatInput
                maxPostLength={maxPostLength}
                memes={memes}
                submitPost={submitPost}
                message={message}
                setMessage={setMessage}
            />
            <ChatPosts
                posts={posts}
                previousPagePostId={previousPagePostId}
                nextPagePostId={nextPagePostId}
                isModerator={isModerator}
                deletePost={deletePost}
                quotePost={quotePost}
                goToNextPage={() => changePage(nextPagePostId)}
                goToPreviousPage={() => changePage(previousPagePostId)}
                goToLatestPage={() => changePage(latestPostId)}
                postsBehind={currentPagePostIdRef.current > 0 ? latestPostId - currentPagePostIdRef.current : initialPostIdRef.current > 0 ? latestPostId - initialPostIdRef.current : 0}
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

function ChatInput({maxPostLength, memes, submitPost, message, setMessage}) {
    const [quickReply, _setQuickReply] = React.useState(
        JSON.parse(localStorage.getItem("quick_reply_on") ?? "true")
    );
    const [showMemeSelect, setShowMemeSelect] = React.useState(false);


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
    const charactersRemainingDisplay = `Characters remaining ${charactersRemaining}`;

    return (
        <div className="chat_input_container">
            {showMemeSelect &&
                <ChatMemeModal
                    memes={memes}
                    selectMeme={handleMemeSelect}
                    closeMemeSelect={() => setShowMemeSelect(false)}
                />
            }
            <div className="chat_input_left">
                <div className="chat_settings_container">
                    <label className={"quick_reply_label ft-s ft-c1 ft-medium"}>quick-reply</label>
                    <input className="quick_reply_box" type="checkbox" checked={quickReply} onChange={e => setQuickReply(e.target.checked)} />
                </div>
                <div className="chat_meme_toggle_container">
                    <button className={"meme_toggle button-bar_large ft-s ft-c1 ft-medium t-hover"} onClick={() => setShowMemeSelect(!showMemeSelect)}>meme list</button>
                </div>
            </div>
            <div className="chat_input_center">
                <textarea
                    id="chat_input_box"
                    minLength="3"
                    maxLength={maxPostLength}
                    value={message}
                    onChange={e => setMessage(e.target.value)}
                    onKeyDown={handleKeyDown}
                ></textarea>
                <span className="ft-s ft-c1">{charactersRemainingDisplay}</span>
            </div>
            <div className="chat_input_right">
                <button className={"chat_submit_button button-bar_large ft-s ft-c1 ft-medium t-hover"} onClick={handlePostSubmit}>post</button>
            </div>
        </div>
    )
}

function ChatMemeModal({ memes, selectMeme, closeMemeSelect }) {
    return (
        <div id="meme_modal">
            <div className="meme_modal_header">
            </div>
            <div>
                <div id="meme_box">
                    {memes.codes.map((meme, i) => (
                        <div key={`meme:${i}`} className="meme_select">
                            <img src={memes.urls[i]} onClick={() => selectMeme(i)} />
                        </div>
                    ))}
                </div>
            </div>
            <div className="meme_modal_footer">
                <button className={"meme_close button-bar_large ft-s ft-c1 ft-medium t-hover"} onClick={closeMemeSelect}>close</button>
            </div>
        </div>
    );
}

function ChatPosts({
    posts,
    previousPagePostId,
    nextPagePostId,
    isModerator,
    deletePost,
    quotePost,
    goToPreviousPage,
    goToNextPage,
    goToLatestPage,
    postsBehind,
}) {
    return (
        <>  {(postsBehind > 200) &&
                <div id="chat_navigation_latest">
                    <a className="chat_pagination" onClick={goToLatestPage}>{"<< Jump To Latest >>"}</a>
                </div>
            }
            <div id="chat_navigation_top">
                <div className="chat_navigation_divider_left">
                  <svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg>
                </div>
                <div className="chat_pagination_wrapper">{previousPagePostId != null && <a className="chat_pagination" onClick={goToPreviousPage}>{"<< Newer"}</a>}</div>
                <div className="chat_navigation_divider_middle"><svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg></div>
                <div className="chat_pagination_wrapper">
                {nextPagePostId != null &&
                    <>
                    <a className="chat_pagination" onClick={goToNextPage}>{"Older >>"}</a>
                    </>
                }
                </div>
                <div className="chat_navigation_divider_right"><svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg></div>
            </div>
            <div className="chat_post_container">
                {posts.length < 1 &&
                    <div style={{ textAlign: "center" }}>No posts!</div>
                }
                {posts.map((post, i) => (
                    <div key={`post:${i}`} className="chat_post" style={{ textAlign: "center" }}>
                        <div className="post_user_container">
                            <div className="post_user_wrapper">
                                <div className={"post_user_avatar_wrapper " + post.avatarStyle + " " + post.userLinkClassNames.join(' ')}>
                                    <img className={"post_user_avatar_img " + post.avatarStyle} src={post.avatarLink} />
                                </div>
                                <div className="post_user_info">
                                    <a href={post.userProfileLink} className={"chat_user_name " + post.userLinkClassNames.join(' ')}>
                                        {post.userName}
                                    </a>
                                    <div className="post_village_title">
                                        {post.userTitle.toLowerCase()}
                                        <img
                                            className='chat_village_icon'
                                            src={`./images/village_icons/${post.userVillage.toLowerCase()}.png`}
                                            alt={`${post.userVillage} Village`}
                                            title={`${post.userVillage} Village`}
                                        />
                                    </div>
                                    {post.staffBannerName &&
                                        <div className="post_staff_title" style={{ backgroundColor: post.staffBannerColor }}>
                                            {post.staffBannerName}
                                        </div>
                                    }
                                </div>
                            </div>
                        </div>
                        <div className="post_message_container ft-s ft-c3 ft-default" data-id={post.id} dangerouslySetInnerHTML={{ __html: post.message }}>
                        </div>
                        <div className="post_controls_container ft-s ft-c3">
                            <div className="post_controls_wrapper">
                                <div className='post_quote_wrapper'><img
                                    className='post_quote'
                                    src='../images/v2/icons/quote_hover.png'
                                    onClick={() => quotePost(post.id)}
                                /></div>
                                {isModerator &&
                                    <div className='post_delete_wrapper'>
                                        <img
                                            className='post_delete'
                                            src='../images/v2/icons/delete_hover.png'
                                            onClick={() => deletePost(post.id)}
                                        />
                                    </div>
                                    
                                }
                                <div className='post_report_wrapper'>
                                    <a className='imageLink' href={post.reportLink}>
                                        <img className='post_report' src='../images/v2/icons/report_hover.png' />
                                    </a>
                                </div>
                            </div>
                            <div className="post_timestamp">
                                <div style={{ marginBottom: "2px" }}>{post.timeString}</div>
                            </div>
                        </div>
                    </div>
                ))}
            </div>
            <div id="chat_navigation_bottom">
                <div className="chat_navigation_divider_left">
                    <svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg>
                </div>
                <div className="chat_pagination_wrapper">{previousPagePostId != null && <a className="chat_pagination" onClick={goToPreviousPage}>{"<< Newer"}</a>}</div>
                <div className="chat_navigation_divider_middle"><svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg></div>
                <div className="chat_pagination_wrapper">
                    {nextPagePostId != null &&
                        <>
                            <a className="chat_pagination" onClick={goToNextPage}>{"Older >>"}</a>
                        </>
                    }
                </div>
                <div className="chat_navigation_divider_right"><svg width="100%" height="2"><line x1="0%" y1="1" x2="100%" y2="1" stroke="#4e4535" strokeWidth="1"></line></svg></div>
            </div>
        </>
    );
}

window.Chat = Chat;