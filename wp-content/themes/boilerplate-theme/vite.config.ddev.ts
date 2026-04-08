import type { UserConfig } from 'vite';

/**
 * Vite server configuration for DDEV.
 *
 * The custom environment variables are defined in `.ddev/config.yaml`
 * and can be customized `.ddev/config.local.yaml`.
 */

export default {
    server: {
        host: '0.0.0.0',
        // Use a strict port because we have to hard code this in vite.php
        strictPort: true,
        // It's the same as container_port in .ddev/config.yaml
        port: parseInt(process.env.VITE_SERVER_PRIVATE_PORT),
        origin: `${process.env.VITE_SERVER_URL}:${process.env.VITE_SERVER_PORT}`,
        allowedHosts: true,
        cors: true,
    },
} as UserConfig;
