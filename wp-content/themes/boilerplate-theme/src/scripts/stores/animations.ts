import { persistentAtom } from '@nanostores/persistent';
import { $prefersReducedMotion } from './prefers-reduced-motion';

const defaultAnimationsReduced = $prefersReducedMotion.get();

export const isAnimationsReduced = persistentAtom('isAnimationsReduced', defaultAnimationsReduced, {
    encode: JSON.stringify,
    decode: JSON.parse
});
