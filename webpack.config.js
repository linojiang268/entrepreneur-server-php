const path = require('path'),
    ExtractTextPlugin = require("extract-text-webpack-plugin");

module.exports = {
    entry: {
        weixin: ['./resources/assets/wap/weixin/js/app.js', './resources/assets/wap/weixin/css/app.scss'],
    },
    output: {
        path: path.resolve(__dirname, './public/assets'),
        filename: 'js/wap/[name].js'
    },
    externals: {
        //'jquery': '$',
    },
    module: {
        loaders: [
            {
                test: /\.(js|jsx)$/,
                loaders: ['babel?presets[]=es2015,presets[]=react'],
                exclude: /(node_modules|bower_components)/
            },
            {
                test: /\.css/,
                loader: ExtractTextPlugin.extract("style-loader", "css-loader")
            },
            {
                test: /\.scss$/,
                loader: ExtractTextPlugin.extract("style-loader", "css-loader!sass-loader")
            }
        ]
    },
    plugins: [
        new ExtractTextPlugin('css/wap/[name].css', {
            allChunks: true
        })
    ]
};