/*
 ============================================================================
 Name        : client.c
 Author      : xMing
 Version     : 1.0.0_alpha
 Description : A client for cpan
 ============================================================================
 */
#include <stdio.h>
#include <stdlib.h>
//read,write,close等文件操作在此定义
#include <unistd.h>
//一切皆socket
#include <sys/socket.h>
//错误errno
#include <errno.h>
//IPPROTO_TCP常量在此定义
#include <netinet/in.h>
//inet_addr()在此定义
#include <arpa/inet.h>
//gethostbyname()在此定义
#include <netdb.h>
//字符串操作
#include <string.h>
//查看文件大小
#include <sys/stat.h>
#define check_status(sock,code) \
  int time = 0; \
  char output[16]; \
  while(1){ \
    time++; \
    if(time > 5){ \
      puts("连接超时\n"); \
      return 1; \
    } \
    if(read(sock,output,4) > 0){ \
      if(strcmp(output,code) == 0){ \
        break; \
      } \
      memset(output, 0, strlen(output)*sizeof(char)); \
    } \
    sleep(1); \
  }
int main(int argc,char** argv)
{
  char* password;
  if(argc < 2){
    printf("使用方法: %s [文件名] [密码](默认为0)\n",argv[0]);
    return 1;
  }else if(argc == 2){
    password = "0";
  }else{
    password = argv[2];
  }
  if(access(argv[1],0) == -1){
    printf("%s文件不存在\n",argv[1]);
    return 1;
  }
  puts("z");
  //套接字
  int sock;
  //连接服务器时用
  struct sockaddr_in addr_in;
  //创建套接字
  if((sock = socket(AF_INET,SOCK_STREAM,IPPROTO_TCP)) < 0){
    printf("创建套接字失败: %s(errno: %d)\n", strerror(errno),errno);
    return 1;
  }
  addr_in.sin_family = AF_INET; /* 主机字节序 */
  //设置目标服务器端口
  addr_in.sin_port = htons(2333); /*short, 网络字节序 */
  //设置目标服务器
  struct hostent *hp;
  //支持域名和ip的方法
  hp = gethostbyname("35.237.187.165");
  /* memcpy()详解:
     函数原型: void *memcpy(void *dest, const void *src, size_t n);
     功能: 从源src所指的内存地址的起始位置开始拷贝n个字节到目标dest所指的内存地址的起始位置中
     返回值:指向dest的指针
  */
  memcpy(&addr_in.sin_addr.s_addr, hp->h_addr_list[0], (size_t)hp->h_length);
  puts("x");
  //连接服务器
  if(connect(sock,(struct sockaddr*) &addr_in,sizeof(addr_in)) < 0){
    printf("连接失败: %s(errno: %d)\n",strerror(errno),errno);
    return 1;
  }
  puts("a");
  check_status(sock,"0000");
  puts("b");
  FILE *fp=fopen(argv[1],"rb");
  if(fp==NULL){
	    puts("文件读取失败\n");
    	return 1;
  }
  puts("c");
  fseek(fp, 0L, SEEK_END);
  unsigned int fileLen = ftell(fp);
  printf("%d\n",fileLen);
  char *json = (char *) malloc(sizeof(char) * fileLen);
  puts("e");
  fseek(fp, 0, SEEK_SET);
  puts("f");
  fread(json, fileLen, sizeof(char), fp);
  puts("g");
  fclose(fp);
  puts("d");
  sprintf(json,"{\"name\":\"%s\",\"length\":%d,\"password\":\"%s\"}",argv[1],fileLen,password);
  printf("%s",json);
  write(sock,json,strlen(json));
  free(json);
  puts("ok\n");
  close(sock);
  return 0;
}