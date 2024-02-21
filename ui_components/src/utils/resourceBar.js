export function ResourceBar({current_amount, max_amount, resource_type}) {
    const resource_percent = Math.round((current_amount / max_amount) * 100);
    return (
        <div className={'resourceBarOuter ' + resource_type + 'Preview'}>
            <div className={'resourceFill ' + resource_type} style={{ width: resource_percent + "%" }}></div>
            <div className='text'>{current_amount} / {max_amount}</div>
        </div>
    );
}