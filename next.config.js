/** @type {import('next').NextConfig} */
const nextConfig = {
  reactStrictMode: true,
  // Support server-side node modules like better-sqlite3 and pg
  webpack: (config, { isServer }) => {
    const path = require('path');
    config.resolve.alias['@'] = path.resolve(__dirname, 'src');
    if (!isServer) {
      config.resolve.fallback = {
        ...config.resolve.fallback,
        fs: false,
        net: false,
        tls: false,
        crypto: false,
      };
    }
    return config;
  },
};

module.exports = nextConfig;
