export function lerp(start: number, end: number, amt: number): number {
    return (1 - amt) * start + amt * end;
}

export function map(value: number, min0: number, max0: number, min1: number, max1: number): number {
    return min1 + ((value - min0) / (max0 - min0)) * (max1 - min1);
}
