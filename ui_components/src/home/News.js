// @flow

import { apiFetch } from "../utils/network.js";
import type { NewsPostType } from "./newsSchema.js";

type Props = {|
    +initialNewsPosts: $ReadOnlyArray<NewsPostType>,
    +isAdmin: boolean
|};
export function News({ initialNewsPosts, isAdmin }: Props) {
    const [activePostId, setActivePostId] = React.useState(initialNewsPosts[0] !== "undefined" ? initialNewsPosts[0].post_id : null);
    const [editPostId, setEditPostId] = React.useState(null);
    const [numPosts, setNumPosts] = React.useState(initialNewsPosts.length);
    const [newsPosts, setNewsPosts] = React.useState(initialNewsPosts);
    const titleRef = React.useRef(null);
    const versionRef = React.useRef(null);
    const contentRef = React.useRef(null);
    const updateTagRef = React.useRef(null);
    const bugfixTagRef = React.useRef(null);
    const eventTagRef = React.useRef(null);

    function formatNewsDate(ticks) {
        const date = new Date(ticks * 1000);
        return date.toLocaleDateString('en-US', {
            month: '2-digit',
            day: '2-digit',
            year: '2-digit'
        });
    }

    function cleanNewsContents(contents) {
        console.log(contents);
        const parser = new DOMParser();
        return parser.parseFromString(
            contents.replace(/[\r\n]+/g, " ").replace(/<br\s*\/?>/g, '\n'),
            'text/html'
        ).body.textContent;
    }

    function saveNewsItem(postId) {
        console.log(contentRef.current.value);
        apiFetch(homeLinks.news_api, {
            request: 'saveNewsPost',
            post_id: postId,
            title: titleRef.current.textContent,
            version: versionRef.current.textContent,
            content: contentRef.current.value,
            update: updateTagRef.current.checked,
            bugfix: bugfixTagRef.current.checked,
            event: eventTagRef.current.checked,
            num_posts: numPosts,
        }).then(response => {
            if (response.errors.length) {
                console.warn(response.errors);
            }
            else {
                setNewsPosts(response.data.postData);
            }
        })
        setEditPostId(null);
    }

    function NewsItem({ newsItem }) {
        return (
            <div className="news_item">
                <div
                    className={activePostId === newsItem.post_id
                        ? "news_item_header"
                        : "news_item_header news_item_header_minimized"
                    }
                    onClick={() => setActivePostId(newsItem.post_id)}
                >
                    <div className="news_item_title">{newsItem.title.toUpperCase()}</div>
                    <div className="news_item_version">{newsItem.version && newsItem.version.toUpperCase()}</div>
                    {newsItem.tags.map((tag, index) => (
                        <div key={index} className="news_item_tag_container">
                            <div className="news_item_tag_divider">/</div>
                            <div className="news_item_tag">{tag.toUpperCase()}</div>
                        </div>
                    ))}
                    <div className="news_item_details_container">
                        {isAdmin &&
                            <div className="news_item_edit" onClick={() => setEditPostId(newsItem.post_id)}>EDIT</div>
                        }
                        <div className="news_item_details">POSTED {formatNewsDate(newsItem.time)} BY {newsItem.sender.toUpperCase()}</div>
                    </div>
                </div>
                {activePostId === newsItem.post_id &&
                    <>
                        <div className="news_item_banner"></div>
                        <div className="news_item_content" dangerouslySetInnerHTML={{ __html: newsItem.message }}></div>
                    </>
                }
            </div>
        );
    }

    function NewsItemEdit({ newsItem }) {
        return (
            <div className="news_item_editor">
                <div
                    className={activePostId === newsItem.post_id
                        ? "news_item_header"
                        : "news_item_header news_item_header_minimized"
                    }
                    onClick={() => setActivePostId(newsItem.post_id)}
                >
                    <div
                        className="news_item_title"
                        ref={titleRef}
                        contentEditable="true"
                        suppressContentEditableWarning={true}
                    >{newsItem.title.toUpperCase()}</div>
                    <div
                        className="news_item_version"
                        ref={versionRef}
                        contentEditable="true"
                        suppressContentEditableWarning={true}
                    >{newsItem.version && newsItem.version.toUpperCase()}</div>
                    <div className="news_item_tag_container">
                        <div className="news_item_tag_divider">/</div>
                        <div className="news_item_tag">UPDATE</div>
                        <input id="news_tag_update" type="checkbox" ref={updateTagRef} defaultChecked={newsItem.tags.includes("update")} />
                    </div>
                    <div className="news_item_tag_container">
                        <div className="news_item_tag_divider">/</div>
                        <div className="news_item_tag">BUG FIXES</div>
                        <input id="news_tag_bugfixes" type="checkbox" ref={bugfixTagRef} defaultChecked={newsItem.tags.includes("bugfix")}/>
                    </div>
                    <div className="news_item_tag_container">
                        <div className="news_item_tag_divider">/</div>
                        <div className="news_item_tag">EVENT</div>
                        <input id="news_tag_event" type="checkbox" ref={eventTagRef} defaultChecked={newsItem.tags.includes("event")}/>
                    </div>
                    <div className="news_item_details_container">
                        {isAdmin &&
                            <>
                                <div className="news_item_edit" onClick={() => setEditPostId(null)}>CANCEL</div>
                                <div className="news_item_edit" onClick={() => saveNewsItem(newsItem.post_id)}>SAVE</div>
                            </>
                        }
                        <div className="news_item_details">POSTED {formatNewsDate(newsItem.time)} BY {newsItem.sender.toUpperCase()}</div>
                    </div>
                </div>
                {activePostId === newsItem.post_id &&
                    <>
                        <div className="news_item_banner"></div>
                        <textarea className="news_item_content_editor" ref={contentRef} defaultValue={cleanNewsContents(newsItem.message)} />
                    </>
                }
            </div>
        );
    }

    return <div className="news_posts_container">
        {newsPosts && newsPosts.map((newsItem) => (
            (newsItem.post_id === editPostId)
                ? <NewsItemEdit key={`news_post:${newsItem.post_id}`} newsItem={newsItem} />
                : <NewsItem key={`news_post:${newsItem.post_id}`} newsItem={newsItem} />
        ))}
    </div>;
}