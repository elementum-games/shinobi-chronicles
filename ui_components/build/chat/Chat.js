import { apiFetch } from "../utils/network.js";
const chatRefreshInterval = 5000;
function Chat({
  chatApiLink,
  initialPosts,
  initialNextPagePostId,
  initialLatestPostId,
  maxPostLength,
  isModerator,
  initialBanInfo,
  memes
}) {
  const [banInfo, setBanInfo] = React.useState(initialBanInfo);
  const [posts, setPosts] = React.useState(initialPosts);
  const [nextPagePostId, setNextPagePostId] = React.useState(initialNextPagePostId);
  const [latestPostId, setLatestPostId] = React.useState(initialLatestPostId);
  // Only set if we're paginating
  const [previousPagePostId, setPreviousPagePostId] = React.useState(null);
  const currentPagePostIdRef = React.useRef(null);
  const [error, setError] = React.useState(null);
  if (banInfo.isBanned) {
    return /*#__PURE__*/React.createElement(ChatBanInfo, {
      banName: banInfo.banName,
      banDescription: banInfo.banDescription,
      banTimeRemaining: banInfo.banTimeRemaining
    });
  }
  const refreshChat = function () {
    if (currentPagePostIdRef.current != null) {
      return;
    }
    apiFetch(chatApiLink, {
      request: 'load_posts'
    }).then(handleApiResponse);
  };
  React.useEffect(() => {
    const intervalId = setInterval(refreshChat, chatRefreshInterval);
    return () => clearInterval(intervalId);
  }, []);
  function submitPost(message) {
    apiFetch(chatApiLink, {
      request: 'submit_post',
      message: message
    }).then(handleApiResponse);
  }
  function deletePost(postId) {
    apiFetch(chatApiLink, {
      request: 'delete_post',
      post_id: postId
    }).then(handleApiResponse);
  }
  function changePage(newStartingPostId) {
    if (newStartingPostId === currentPagePostIdRef.current) {
      return;
    }
    if (newStartingPostId == null || newStartingPostId >= latestPostId) {
      currentPagePostIdRef.current = null;
      apiFetch(chatApiLink, {
        request: 'load_posts'
      }).then(handleApiResponse);
      return;
    }
    if (newStartingPostId === currentPagePostIdRef.current) {
      return;
    }
    currentPagePostIdRef.current = newStartingPostId;
    apiFetch(chatApiLink, {
      request: 'load_posts',
      starting_post_id: newStartingPostId
    }).then(handleApiResponse);
  }
  const handleApiResponse = response => {
    if (response.data.banInfo && response.data.banInfo.isBanned) {
      setBanInfo(response.data.banInfo);
      return;
    }
    if (response.errors.length > 0) {
      setError(response.errors.join(' '));
    } else {
      setError(null);
    }
    if (response.data.posts != null) {
      setPosts(response.data.posts);
    }
    if (response.data.latestPostId != null) {
      setLatestPostId(response.data.latestPostId);
    }
    if (typeof response.data.previousPagePostId != "undefined") {
      setPreviousPagePostId(response.data.previousPagePostId);
    }
    if (typeof response.data.nextPagePostId != "undefined") {
      setNextPagePostId(response.data.nextPagePostId);
    }
  };
  return /*#__PURE__*/React.createElement("div", null, error != null && /*#__PURE__*/React.createElement("p", {
    className: "systemMessage"
  }, error), /*#__PURE__*/React.createElement(ChatInput, {
    maxPostLength: maxPostLength,
    memes: memes,
    submitPost: submitPost
  }), /*#__PURE__*/React.createElement(ChatPosts, {
    posts: posts,
    previousPagePostId: previousPagePostId,
    nextPagePostId: nextPagePostId,
    isModerator: isModerator,
    deletePost: deletePost,
    goToNextPage: () => changePage(nextPagePostId),
    goToPreviousPage: () => changePage(previousPagePostId)
  }));
}
function ChatBanInfo({
  banName,
  banDescription,
  banTimeRemaining
}) {
  return /*#__PURE__*/React.createElement("table", {
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, banName)), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "center"
    }
  }, banDescription, /*#__PURE__*/React.createElement("br", null), banTimeRemaining != null && /*#__PURE__*/React.createElement("span", null, /*#__PURE__*/React.createElement("b", null, "Time Remaining:"), " ", banTimeRemaining), /*#__PURE__*/React.createElement("br", null), "Visit the ", /*#__PURE__*/React.createElement("a", {
    href: "/support.php"
  }, "Support Center"), " to appeal this."))));
}
function ChatInput({
  maxPostLength,
  memes,
  submitPost
}) {
  const [quickReply, _setQuickReply] = React.useState(JSON.parse(localStorage.getItem("quick_reply_on") ?? "true"));
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

  function setQuickReply(newValue) {
    localStorage.setItem("quick_reply_on", JSON.stringify(newValue));
    _setQuickReply(newValue);
  }
  function handleMemeSelect(memeIndex) {
    setMessage(prevMessage => `${prevMessage}${memes.codes[memeIndex]}`);
    setShowMemeSelect(false);
  }
  const handlePostSubmit = React.useCallback(() => {
    submitPost(message);
    // TODO: Only clear this on a successful server response
    setMessage("");
  }, [message, submitPost]);
  const handleKeyDown = React.useCallback(e => {
    if (e.code !== "Enter") {
      return;
    }
    if (quickReply && !e.shiftKey) {
      e.preventDefault();
      setMessage(e.target.value);
      handlePostSubmit();
    }
  }, [quickReply, handlePostSubmit]);
  const charactersRemaining = maxPostLength - message.length;
  const charactersRemainingDisplay = `Characters remaining: ${charactersRemaining} of ${maxPostLength}`;
  return /*#__PURE__*/React.createElement("div", null, showMemeSelect && /*#__PURE__*/React.createElement(ChatMemeModal, {
    memes: memes,
    selectMeme: handleMemeSelect,
    closeMemeSelect: () => setShowMemeSelect(false)
  }), /*#__PURE__*/React.createElement("table", {
    id: "chat_input_table",
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Post Message")), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "center"
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: "meme_toggle",
    onClick: () => setShowMemeSelect(!showMemeSelect)
  }, "Memes"), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("textarea", {
    id: "chat_input_box",
    minLength: "3",
    maxLength: maxPostLength,
    value: message,
    onChange: e => setMessage(e.target.value),
    onKeyDown: handleKeyDown
  }), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("input", {
    type: "checkbox",
    checked: quickReply,
    onChange: e => setQuickReply(e.target.checked)
  }), " Quick reply", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("span", {
    className: "red"
  }, charactersRemaining < 50 && charactersRemainingDisplay), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("button", {
    onClick: handlePostSubmit
  }, "Post"))))));
}
function ChatMemeModal({
  memes,
  selectMeme,
  closeMemeSelect
}) {
  return /*#__PURE__*/React.createElement("table", {
    id: "meme_modal",
    className: "table"
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", null, "Memes")), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("div", {
    id: "meme_box"
  }, memes.codes.map((meme, i) => /*#__PURE__*/React.createElement("div", {
    key: `meme:${i}`,
    className: "meme_select"
  }, /*#__PURE__*/React.createElement("img", {
    src: memes.urls[i],
    onClick: () => selectMeme(i)
  })))))), /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    style: {
      textAlign: "center"
    }
  }, /*#__PURE__*/React.createElement("button", {
    className: "meme_toggle",
    onClick: closeMemeSelect
  }, "Close")))));
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
  return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("table", {
    className: "table",
    style: {
      width: "98%"
    }
  }, /*#__PURE__*/React.createElement("tbody", null, /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("th", {
    style: {
      width: "28%"
    }
  }, "Users"), /*#__PURE__*/React.createElement("th", {
    style: {
      width: "61%"
    }
  }, "Message"), /*#__PURE__*/React.createElement("th", {
    style: {
      width: "10%"
    }
  }, "Time")), posts.length < 1 && /*#__PURE__*/React.createElement("tr", null, /*#__PURE__*/React.createElement("td", {
    colSpan: "3",
    style: {
      textAlign: "center"
    }
  }, "No posts!")), posts.map((post, i) => /*#__PURE__*/React.createElement("tr", {
    key: `post:${i}`,
    className: "chat_msg",
    style: {
      textAlign: "center"
    }
  }, /*#__PURE__*/React.createElement("td", null, /*#__PURE__*/React.createElement("div", {
    id: "user_data_container"
  }, /*#__PURE__*/React.createElement("div", {
    className: "avatarContainer"
  }, /*#__PURE__*/React.createElement("img", {
    src: post.avatarLink
  })), /*#__PURE__*/React.createElement("div", {
    className: "character_info"
  }, /*#__PURE__*/React.createElement("a", {
    href: post.userProfileLink,
    className: post.userLinkClassNames.join(' ')
  }, post.userName), /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("p", null, /*#__PURE__*/React.createElement("img", {
    className: "villageIcon",
    src: `./images/village_icons/${post.userVillage.toLowerCase()}.png`,
    alt: `${post.userVillage} Village`,
    title: `${post.userVillage} Village`
  }), post.userTitle))), post.staffBannerName && /*#__PURE__*/React.createElement("p", {
    className: "staffMember",
    style: {
      backgroundColor: post.staffBannerColor
    }
  }, post.staffBannerName)), /*#__PURE__*/React.createElement("td", {
    dangerouslySetInnerHTML: {
      __html: post.message
    }
  }), /*#__PURE__*/React.createElement("td", {
    style: {
      fontStyle: "italic"
    }
  }, /*#__PURE__*/React.createElement("div", {
    style: {
      marginBottom: "2px"
    }
  }, post.timeString), isModerator && /*#__PURE__*/React.createElement("img", {
    className: "delete_post_icon small_image",
    src: "../images/delete_icon.png",
    onClick: () => deletePost(post.id)
  }), /*#__PURE__*/React.createElement("a", {
    className: "imageLink",
    href: post.reportLink
  }, /*#__PURE__*/React.createElement("img", {
    className: "small_image",
    src: "../images/report_icon.png"
  }))))))), /*#__PURE__*/React.createElement("p", {
    style: {
      textAlign: "center"
    }
  }, previousPagePostId != null && /*#__PURE__*/React.createElement("a", {
    className: "paginationLink",
    onClick: goToPreviousPage
  }, "Previous"), nextPagePostId != null && /*#__PURE__*/React.createElement(React.Fragment, null, previousPagePostId != null && /*#__PURE__*/React.createElement("span", null, "\xA0\xA0|\xA0\xA0"), /*#__PURE__*/React.createElement("a", {
    className: "paginationLink",
    onClick: goToNextPage
  }, "Next"))));
}
window.Chat = Chat;