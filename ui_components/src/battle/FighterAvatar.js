

const styles = {
    avatarContainer: {
        width: "100px",
        height: "100px",
    
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        background: "rgba(0,0,0,0.1)",
    },
    avatarImage: {
        display: "block",
        margin: "auto",
    
        /* for alt text */
        fontWeight: "bold",
        textTransform: "uppercase",
    }
};

export function FighterAvatar({ fighterName, avatarLink, maxAvatarSize, includeContainer = true }) {
    const name_words = fighterName.split(' ');
    const name_letters = name_words.map(word => word.slice(0, 1));
    const name_initials = name_letters.join('');

    let alt = '';
    if(name_initials.length > 1) {
        alt = name_initials[0] + name_initials.slice(-1);
    }
    else {
        alt = name_initials;
    }

    const img = (
        <img
            src={avatarLink}
            className='avatarImage'
            style={{
                ...styles.avatarImage,
                maxWidth: `${maxAvatarSize}px`
            }}
            alt={alt}
        />
    );

    if(includeContainer) {
        return (
            <div className='avatarContainer' style={styles.avatarContainer}>
                {img}
            </div>
        )
    }
    else {
        return img;
    }
}