FROM node:18-alpine

# Set working directory
WORKDIR /usr/src/app

# Install dependencies
RUN apk --no-cache add \
    gnupg \
    wget \
    chromium \
    nss \
    freetype \
    freetype-dev \
    harfbuzz \
    ca-certificates \
    ttf-freefont \
    udev \
    nodejs \
    npm

# Set environment variables
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD true
#ENV CHROME_BIN=/usr/bin/chromium
ENV CHROME_BIN=/usr/bin/chromium-browser
ENV PUPPETEER_EXECUTABLE_PATH /usr/bin/chromium-browser

# Copy package.json and install npm packages
COPY package*.json ./

RUN npm install

# Copy the rest of the application files
COPY .  .

# Expose ports
EXPOSE 4000

CMD ["npm", "run", "start"]
